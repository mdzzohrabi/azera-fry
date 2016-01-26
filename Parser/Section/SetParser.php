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

class SetParser implements ParserInterface
{

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return bool
     */
    public function canParse(Token $token, Parser $parser)
    {
        return $token->test( null , 'set' );
    }

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return Node
     */
    public function parse(Token $token, Parser $parser)
    {

        $stream = $parser->getStream();

        $stream->expect( TokenTypes::T_SECTION_TYPE , 'set' );

        $var = $stream->expect( TokenTypes::T_NAME , null , 'Variable name is empty' );

        if ( $stream->test( TokenTypes::T_SET ) ) {

            $stream->expect(TokenTypes::T_SET);
            $exp = $parser->parseExpression();
            $stream->expect(TokenTypes::T_SECTION_OPEN);

            return new Node\SetNode( $var->getValue() , $exp , false , $token->getLine() );

        } else {

            $stream->expect( TokenTypes::T_SECTION_OPEN , '{' );
            $value = $parser->subparse([ $this , 'decideIfEnd' ]);
            $stream->expect( TokenTypes::T_SECTION_CLOSE , '}' );

            return new Node\SetNode( $var->getValue() , $value , true , $token->getLine() );

        }


    }

    public function decideIfEnd( TokenStream $stream ) {
        return $stream->test( TokenTypes::T_SECTION_CLOSE , '}' );
    }

}