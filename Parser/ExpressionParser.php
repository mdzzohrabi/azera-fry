<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Parser;


use Azera\Fry\Exception\SyntaxException;
use Azera\Fry\Node;
use Azera\Fry\Parser;
use Azera\Fry\Token;
use Azera\Fry\TokenStream;
use Azera\Fry\TokenTypes;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Class ExpressionParser
 *
 * Im used "Precedence climbing" algorithm for this parser
 *
 * @see http://www.engr.mun.ca/~theo/Misc/exp_parsing.htm
 *
 * @package Azera\Fry\Parser
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class ExpressionParser
{

    const OPERATOR_LEFT = 1;
    const OPERATOR_RIGHT = 2;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var TokenStream
     */
    protected $stream;

//    protected $binaryOperators = [
//        '~' => [ 'precedence'   => 20 , 'associativity' => self::OPERATOR_LEFT ],
//        '+' => [ 'precedence'   => 20 , 'associativity' => self::OPERATOR_LEFT ],
//        '-' => [ 'precedence'   => 20 , 'associativity' => self::OPERATOR_LEFT ],
//        '/' => [ 'precedence'   => 60 , 'associativity' => self::OPERATOR_LEFT ],
//        '*' => [ 'precedence'   => 60 , 'associativity' => self::OPERATOR_LEFT ],
//        '>' => [ 'precedence'   => 10 , 'associativity' => self::OPERATOR_LEFT ],
//        '<' => [ 'precedence'   => 10 , 'associativity' => self::OPERATOR_LEFT ],
//        '<=' => [ 'precedence'   => 10 , 'associativity' => self::OPERATOR_LEFT ],
//        '>=' => [ 'precedence'   => 10 , 'associativity' => self::OPERATOR_LEFT ],
//        '==' => [ 'precedence'   => 10 , 'associativity' => self::OPERATOR_LEFT ],
//        'equals' => [ 'precedence'   => 10 , 'associativity' => self::OPERATOR_LEFT ],
//    ];
//
//    protected $unaryOperators  = [
//
//    ];

    public function __construct( Parser $parser )
    {
        $this->parser = $parser;
        $this->stream = $parser->getStream();
    }

    public function getEnvironment() {
        return $this->parser->getEnvironment();
    }

    public function getBinaryOperator( $name ) {
        return $this->parser->getEnvironment()->getBinaryOperator( $name );
    }

    public function getUnaryOperator( $name ) {
        return $this->parser->getEnvironment()->getUnaryOperator( $name );
    }

    public function hasBinaryOperator( $name ) {
        return $this->parser->getEnvironment()->hasBinaryOperator( $name );
    }

    public function hasUnaryOperator( $name ) {
        return $this->parser->getEnvironment()->hasUnaryOperator( $name );
    }

    public function parseExpression( $precedence = 0 )
    {

        $expr = $this->getPrimary();
        $token = $this->stream->getToken();

        if ( $token->is( TokenTypes::T_OPERATOR ) && !$this->hasBinaryOperator( $token->getValue() ) && !$this->hasUnaryOperator( $token->getValue() ) )
            throw new ParseException( sprintf('Operator "%s" not defined for engine' , $token->getValue() ) );

        # Binary operators
        while ( $this->isBinary( $token ) && $this->binaryPrecedence( $token ) >= $precedence ) {

            $op = $this->getBinaryOperator( $token->getValue() );
            $this->stream->next();
            $expr1 = $this->parseExpression( $op->getAssociativity() == self::OPERATOR_LEFT ? $op->getPrecedence() + 1 : $op->getPrecedence() );

            $operatorNodeClass = $op->getClass();

            $expr = new $operatorNodeClass( $expr , $expr1 , $token->getValue() , $token->getLine() );

            $token = $this->stream->getCurrent();

        }

        return $expr;
    }

    protected function getPrimary() {

        $token = $this->stream->getToken();

        // Unary operators
        if ( $this->isUnary( $token ) ) {

            $operator = $this->getUnaryOperator( $token->getValue() );
            $this->stream->next();
            $expr = $this->parseExpression( $operator->getPrecedence() );
            return $expr;

        // Open parenthesis
        } elseif ( $token->test( TokenTypes::T_OPEN_PARAN ) ) {

            $this->stream->next();
            $expr = $this->parseExpression();
            $this->stream->expect( TokenTypes::T_CLOSE_PARAN );
            return $expr;

        // Normal token
        } else {
            return $this->parsePrimary();
        }

    }

    protected function parsePrimary() {

        $node = null;
        $token = $this->stream->getCurrent();

        switch ( $token->getType() ) {

            case TokenTypes::T_STR:
                $node = $this->parseString();
                break;

            case TokenTypes::T_NUM:
                $node = $this->parseNumber();
                break;

            case TokenTypes::T_BOOLEAN:
                $node = $this->parseBoolean();
                break;

            case TokenTypes::T_NULL:
                $node = $this->parseNull();
                break;

            case TokenTypes::T_NAME:
                if ( $this->stream->nextIf( TokenTypes::T_OPEN_PARAN ) ) {
                    // Call
                    $node = $this->parseFunction( $token->getName() , $token->getLine() );
                } else {
                    // Variable
                    $this->stream->next();
                    $node = new Node\Expression\Name( $token->getValue() , $token->getLine() );
                }
                break;

            case TokenTypes::T_OPEN_BRACE:
            case TokenTypes::T_OPEN_BRACKET:
                $node = $this->parseArray();
                break;

        }

        return $this->parsePostfix( $node );

    }

    protected function parseArray() {

        $token = $this->stream->getCurrent();

        if ( $token->is( TokenTypes::T_OPEN_BRACKET ) ) {
            $openToken = TokenTypes::T_OPEN_BRACKET;
            $closeToken = TokenTypes::T_CLOSE_BRACKET;
        } else {
            $openToken = TokenTypes::T_OPEN_BRACE;
            $closeToken = TokenTypes::T_CLOSE_BRACE;
        }

        $arrayType = $token->is( TokenTypes::T_OPEN_BRACE ) ? 'BRACE' : 'BRACKET';
        $allowKey = ( $arrayType == 'BRACE' );
        $elements = [];
//        $stack = [];
        $first = true;

        $this->stream->expect( $openToken );

        while ( !$this->stream->test( $closeToken ) ) {

            if ( !$first )
                $this->stream->expect( TokenTypes::T_COMMA , null , 'Array elements must be separate by comma.' );

            $key = $value = null;

            $value = $this->parseExpression();

            if ( $this->stream->test( TokenTypes::T_COLON ) ) {

                if ( !$allowKey )
                    throw new SyntaxException(sprintf('Vector array not allowed'));

                $this->stream->expect( TokenTypes::T_COLON );
                $key = $value;
                $value = $this->parseExpression();

                if ( !$key instanceof Node\Expression\Name && !$key instanceof Node\Expression\Constant ) {
                    throw new SyntaxException(sprintf( 'Invalid array key definition' ));
                }

            }


            $elements[] = [ $key , $value ];

            $first = false;

        }

        $this->stream->expect( $closeToken );

        return new Node\Expression\ArrayNode( $elements , $token->getLine() );


    }

//    protected function parsePrimary() {
//
//        $nodes = [];
//        $operators = [];
//
//        while ( true && !$this->stream->isEOF() ) {
//
//            $token = $this->stream->getToken();
//            $line  = $token->getLine();
//            $node = null;
//
//            switch ($token->getType()) {
//
//                case TokenTypes::T_STR:
//                    $node = $this->parseString();
//                    break;
//
//                case TokenTypes::T_NUM:
//                    $node = $this->parseNumber();
//                    break;
//
//            }
//
//            if ( $node )
//                $nodes[] = $node;
//
//            if ( $node && !$this->stream->isEOF() ) {
//
//                // If next token is operator
//                if ( $this->stream->test(TokenTypes::T_OPERATOR)) {
//
//                    $token = $this->stream->next();
//                    $operators[] = $token->getValue();
//
//                }
//                // Filter
//                else if ( $this->stream->test(TokenTypes::T_FILTER)) {
//
//                    $token = $this->stream->next();
//                    $node = $this->createOperandNode( $nodes , $operators , $token->getLine() );
//                    $nodes = [];
//                    $operators = [];
//                    $node = $this->parseFilter( $node );
//                    $nodes[] = $node;
//
//                } else
//                    break;
//
//            }
//            else
//                break;
//
//        }
//
//        if ( count($nodes) > 1 )
//            return $this->createOperandNode( $nodes , $operators , $line );
//
//        return current($nodes);
//
//    }

    /**
     * @param Node[] $nodes
     * @param array  $operators
     * @param        $line
     * @return Node\Expression\Operand|null
     * @throws SyntaxException
     */
    protected function createOperandNode( array $nodes , array $operators , $line ) {

        if ( empty($nodes) )
            return null;

        if ( count($nodes) - 1 != count($operators) )
            throw new SyntaxException( 'Operands and operators are not equals' );

        return new Node\Expression\Operand( $nodes , $operators , $line );

    }

    protected function parseConstant( $tokenType ) {
        $constant = $this->stream->expect( $tokenType );
        return new Node\Expression\Constant( $constant->getValue() , $constant->getLine() );
    }

    protected function parseNull() {
        $string = $this->stream->expect( TokenTypes::T_NULL );
        return new Node\Expression\Constant( $string->getValue() , $string->getLine() , Node\Expression\Constant::TYPE_KEYWORD );
    }

    protected function parseBoolean() {
        $string = $this->stream->expect( TokenTypes::T_BOOLEAN );
        return new Node\Expression\Constant( $string->getValue() , $string->getLine() , Node\Expression\Constant::TYPE_KEYWORD );
    }

    protected function parseString() {
        $string = $this->stream->expect( TokenTypes::T_STR );
        return new Node\Expression\Constant( $string->getValue() , $string->getLine() , Node\Expression\Constant::TYPE_STRING );
    }

    protected function parseNumber() {
        $num = $this->stream->expect( TokenTypes::T_NUM );
        return new Node\Expression\Constant( $num->getValue() , $num->getLine() , Node\Expression\Constant::TYPE_NUMBER );
    }

    protected function parseFilter( $node ) {

        $this->stream->expect( TokenTypes::T_FILTER );

        while (true) {

            $nameToken = $this->stream->expect( TokenTypes::T_NAME );
            $name      = $nameToken->getValue();

            if ( $this->stream->test(TokenTypes::T_OPEN_PARAN) ) {
                $arguments = $this->parseArguments();
            } else {
                $arguments = new Node\Expression\Arguments();
            }

            if ( $this->stream->nextIf( TokenTypes::T_OPEN_PARAN ) )
                $arguments = $this->parseArguments( false );

            $node = new Node\Expression\Filter( $node , $name , $arguments , $nameToken->getLine() );

            if ( !$this->stream->nextIf( TokenTypes::T_FILTER ) )
                break;

        }

        return $node;

    }

    protected function parseFunction( $name , $line )
    {

        $args = $this->parseArguments( true );

        return new Node\Expression\Call( $name , $args , $line );

    }

    /**
     * @param bool|false $namedArguments
     * @param bool|false $definition
     * @return Node
     * @throws SyntaxException
     */
    public function parseArguments( $namedArguments = false , $definition = false ) {

        $args = [];
        $openParan = $this->stream->expect( TokenTypes::T_OPEN_PARAN );
        $line = $openParan->getLine();

        $allowNonNamedArgs = true;

        while ( !$this->stream->test(TokenTypes::T_CLOSE_PARAN) ) {

            if ( !empty( $args ) )
                $this->stream->expect( TokenTypes::T_COMMA , ',' , 'Arguments must be separated by comma.' );

            $name = null;
            $value = null;

            if ( $definition ) {

                $token = $this->stream->expect( TokenTypes::T_NAME , null , 'Arguments must have name.' );

                if ( !$this->isSimpleName( $token ) )
                    throw new SyntaxException( 'Invalid argument name , "%s"' , $token->getValue() );

                $name = $token->getValue();

                if ( $this->stream->test( TokenTypes::T_SET ) ) {
                    $this->stream->expect(TokenTypes::T_SET);
                    $value = $this->parseExpression();
                }

            } else {

                if ( $namedArguments && $this->stream->look()->is( TokenTypes::T_SET ) ) {

                    $nameToken = $this->stream->expect( TokenTypes::T_NAME , null , 'Missing argument name.' );
                    if ( !$this->isSimpleName( $nameToken ) )
                        throw new SyntaxException( 'Invalid argument name , "%s"' , $nameToken->getValue() );
                    $name = $nameToken->getValue();
                    $this->stream->expect( TokenTypes::T_SET );

                    $allowNonNamedArgs = false;

                } elseif ( !$allowNonNamedArgs ) {

                    throw new SyntaxException( 'Non-named args must come before named args.' );

                }

                $value = $this->parseExpression();

            }

            if ( $name )
                $args[$name] = $value;
            else
                $args[] = $value;

        }

        $this->stream->expect( TokenTypes::T_CLOSE_PARAN , ')' , 'A list of arguments must be closed by parenthesis' );

        return new Node\Expression\Arguments( $args , $definition , $line );

    }

    protected function parsePostfix( Node $node = null ) {

        while ( true ) {

            $token = $this->stream->getToken();

            if ( $token->test( TokenTypes::T_FILTER ) ) {

                $node = $this->parseFilter($node);

            } else {

                break;

            }

        }

        return $node;

    }

    protected function isSimpleName( Token $token ) {
        return strpos( $token->getValue() , '.' ) === false && strpos( $token->getValue() , '[' ) === false;
    }

    /**
     * @param Token $token
     * @return bool
     */
    protected function isUnary( Token $token ) {
        return $token->getType() == TokenTypes::T_OPERATOR && $this->hasUnaryOperator( $token->getValue() );
    }

    /**
     * @param Token $token
     * @return bool
     */
    protected function isBinary( Token $token ) {
        return $token->getType() == TokenTypes::T_OPERATOR && $this->hasBinaryOperator( $token->getValue() );
    }

    /**
     * @param Token $token
     * @return int
     */
    protected function binaryPrecedence( Token $token ) {
        return $this->getBinaryOperator( $token->getValue() )->getPrecedence() ?: 20;
    }

    /**
     * @param Token $token
     * @return int
     */
    protected function unaryPrecedence( Token $token ) {
        return $this->getUnaryOperator( $token->getValue() )->getPrecedence() ?: 10;
    }

}