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

class MacroParser implements ParserInterface
{

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return bool
     */
    public function canParse(Token $token, Parser $parser)
    {
        return $token->test( TokenTypes::T_SECTION_TYPE , 'macro' );
    }

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return Node
     */
    public function parse(Token $token, Parser $parser)
    {

        $stream = $parser->getStream();

        $stream->expect( TokenTypes::T_SECTION_TYPE , 'macro' );

        $name = $stream->expect( TokenTypes::T_NAME , null , 'Missing macro name' );
        $args = null;

        if ( $stream->test( TokenTypes::T_OPEN_PARAN ) ) {
            $args = $parser->getExpressionParser()->parseArguments( false , true );
        }

        $stream->expect( TokenTypes::T_SECTION_OPEN , '{' , 'Missing macro block open brace.' );

        $body = $parser->subparse([ $this , 'decideMacroEnd' ]);


        $stream->expect( TokenTypes::T_SECTION_CLOSE , '}' );

        $parser->setMacro(
            $name->getValue() ,
            new Node\Macro( $name->getValue() , $args , $body , $token->getLine() )
        );

    }

    public function decideMacroEnd( TokenStream $stream ) {
        return $stream->test( TokenTypes::T_SECTION_CLOSE , '}' );
    }

}