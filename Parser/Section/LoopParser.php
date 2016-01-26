<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Parser\Section;

use Azera\Fry\Node;
use Azera\Fry\Parser;
use Azera\Fry\Parser\ParserInterface;
use Azera\Fry\Token;
use Azera\Fry\TokenStream;
use Azera\Fry\TokenTypes;

class LoopParser implements  ParserInterface
{

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return bool
     */
    public function canParse(Token $token, Parser $parser)
    {
        return $token->test( TokenTypes::T_SECTION_TYPE , 'for' );
    }

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return Node
     */
    public function parse(Token $token, Parser $parser)
    {

        $stream = $parser->getStream();

        $stream->expect( TokenTypes::T_SECTION_TYPE , 'for' );

        $key = $value = null;

        $value = $stream->expect( TokenTypes::T_NAME , null , 'Loop value variable not defined.' );

        if ( $stream->test( TokenTypes::T_COMMA ) ) {
            $stream->expect( TokenTypes::T_COMMA );
            $key = $value;
            $value = $stream->expect( TokenTypes::T_NAME , null , 'Loop value variable not defined.' );
        }

        $stream->expect( TokenTypes::T_KEYWORD , 'in' );

        $repo = $parser->parseExpression();

        $stream->expect( TokenTypes::T_SECTION_OPEN , '{' );

        $body = $parser->subparse( [ $this, 'decideIfEnd' ] );

        $stream->expect( TokenTypes::T_SECTION_CLOSE , '}' );

        return new Node\LoopNode( $key ? $key->getValue() : null , $value->getValue() , $repo , $body , $token->getLine() );

    }

    public function decideIfEnd( TokenStream $stream ) {
        return $stream->test( TokenTypes::T_SECTION_CLOSE , '}' );
    }

}