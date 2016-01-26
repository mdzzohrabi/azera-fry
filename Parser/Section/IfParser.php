<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Parser\Section;


use Azera\Fry\Exception\SyntaxException;
use Azera\Fry\Node;
use Azera\Fry\Parser;
use Azera\Fry\Parser\ParserInterface;
use Azera\Fry\Stream;
use Azera\Fry\Token;
use Azera\Fry\TokenStream;
use Azera\Fry\TokenTypes;

class IfParser implements ParserInterface
{

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return bool
     */
    public function canParse(Token $token, Parser $parser)
    {
        return $token->test( null, [ 'if' ] );
    }

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return Node
     * @throws SyntaxException
     * @throws \Azera\Fry\Exception\Exception
     */
    public function parse(Token $token, Parser $parser)
    {

        $line = $token->getLine();

        $stream = $parser->getStream();
        $stream->expect( TokenTypes::T_SECTION_TYPE , 'if' );
        $expr = $parser->parseExpression();
        $openToken = $stream->expect( TokenTypes::T_SECTION_OPEN );

        $body = $parser->subparse([ $this , $openToken->getValue() == '{' ? 'decideIfBraceEnd' : 'decideIfFork' ]);

        $else = null;
        $tests = [ $expr , $body ];

        if ( $openToken->getValue() == '{' ) {

            $stream->expect( TokenTypes::T_SECTION_CLOSE , '}' );

        } else {

            $end = false;

            while (!$end) {
                $token = $stream->getToken();
                if (!$stream->isEOF()) {
                    $stream->next();
                }
                switch ($token->getValue()) {

                    case 'else':
                        $stream->expect(TokenTypes::T_SECTION_OPEN);
                        $else = $parser->subparse([$this, 'decideIfEnd']);
                        break;

                    case 'elseif':
                        $expr = $parser->parseExpression();
                        $stream->expect(TokenTypes::T_SECTION_OPEN);
                        $body = $parser->subparse([$this, 'decideIfFork']);
                        $tests[] = $expr;
                        $tests[] = $body;
                        break;

                    case 'endif':
                        $stream->expect(TokenTypes::T_SECTION_OPEN);
                        $end = true;
                        break;

                    default:
                        throw new SyntaxException('Unexpected end of template, if statement end not found');

                }
            }

        }

        return new Node\IfSection( $tests , $else , $line );

    }

    protected function parseNamedIf( Parser $parser ) {

    }

    public function decideIfFork( TokenStream $stream ) {
        return $stream->nextIf([ TokenTypes::T_SECTION_TYPE ],[ 'else' , 'endif' , 'elseif' ]);
    }

    public function decideIfEnd( TokenStream $stream ) {
        return $stream->nextIf([ TokenTypes::T_SECTION_TYPE ],[ 'endif' ]);
    }

    public function decideIfBraceEnd( TokenStream $stream ) {
        return $stream->test([ TokenTypes::T_SECTION_CLOSE ],[ '}' ]);
    }
}