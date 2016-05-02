<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry;

use Azera\Fry\Exception\Exception;
use Azera\Fry\Node\Template;
use Azera\Fry\Parser\ExpressionParser;
use Azera\Fry\Parser\ParserInterface;

/**
 * Class Parser
 *
 * @package Azera\Fry
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class Parser
{

    /**
     * @var TokenStream
     */
    protected $stream;

    /**
     * @var ParserInterface[]
     */
    protected $parsers;

    /**
     * @var Node[]
     */
    protected $blocks = [];

    /**
     * @var Node[]
     */
    protected $macros = [];

    /**
     * @var Node
     */
    protected $parent;

    /**
     * @var ExpressionParser
     */
    protected $expressionParser;
    /**
     * @var Environment
     */
    private $environment;

    public function __construct( TokenStream $stream , Environment $environment )
    {

        # Token stream
        $this->stream = $stream;

        # Expression parser
        $this->expressionParser = new ExpressionParser( $this );

        # Parsers
        $this->parsers = [
            new Parser\TextParser(),
            new Parser\PrintParser(),
            new Parser\SectionParser( $environment ),
            new Parser\CommentParser()
        ];

        $this->environment = $environment;
    }

    /**
     * @return TokenStream
     */
    public function getStream() {
        return $this->stream;
    }

    public function parse() {

        $body = $this->subparse();

        return new Template( $this->stream->getFileName() , $this->parent ,  $body , $this->blocks , $this->macros );

    }

    public function subparse( $test = null ) {

        $nodes = [];

        while ( !$this->stream->isEOF() ) {

            $token = $this->stream->getToken();
            $node = null;

            if ( $token->test([ TokenTypes::T_CODE_BLOCK , TokenTypes::T_EOF ]) ) {
                $this->stream->next();
                continue;
            }

            if ( $test && call_user_func( $test , $this->stream ) )
                break;

            $found = false;

            foreach ($this->parsers as $parser)
                if ( $parser->canParse( $token , $this ) ) {
                    $found = true;
                    $node = $parser->parse( $token , $this );
                    break;
                }

            if ( !$found )
                throw new Exception( 'Syntax error, invalid syntax \'%s\' (%s) on line %d column %d' , $token->getValue() , $token->getTokenName() , $token->getLine() , $token->getColumn() );

            if ( $node )
                $nodes[] = $node;

        }

        return new Node( $nodes , [] , 0 );

    }

    public function parseExpression() {
        return $this->expressionParser->parseExpression();
    }

    /**
     * @param Node $parentNode
     * @return $this
     */
    public function setParent( $parentNode ) {
        $this->parent = $parentNode;
        return $this;
    }

    /**
     * @return Node
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @param $name
     * @param $macro
     * @return $this
     */
    public function setMacro( $name , $macro ) {
        $this->macros[ $name ] = $macro;
        return $this;
    }

    /**
     * @param $name
     * @return Node
     */
    public function getMacro( $name ) {
        return $this->macros[ $name ];
    }

    /**
     * @param string $name
     * @param Node $blockNode
     * @return $this
     */
    public function setBlock( $name , $blockNode ) {
        $this->blocks[ $name ] = $blockNode;
        return $this;
    }

    /**
     * @param $name
     * @return Node
     */
    public function getBlock( $name ) {
        return $this->blocks[ $name ];
    }

    /**
     * @return ExpressionParser
     */
    public function getExpressionParser()
    {
        return $this->expressionParser;
    }

    /**
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

}