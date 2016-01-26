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
use Azera\Fry\Token;
use Azera\Fry\TokenStream;
use Azera\Fry\TokenTypes;

class BlockParser implements Parser\ParserInterface
{

    const BLOCK_START = 'block';
    const BLOCK_END = 'endblock';

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return bool
     */
    public function canParse(Token $token, Parser $parser)
    {
        return $token->test( null , [ self::BLOCK_START ] );
    }

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return Node
     */
    public function parse(Token $token, Parser $parser)
    {

        $line = $token->getLine();
        $stream = $parser->getStream();

        $stream->expect( null , [ self::BLOCK_START ] );

        $blockName = $stream->expect( TokenTypes::T_NAME , null , 'Missing block name' );

        $endDecider = 'decideIfEnd';

        if ( $stream->test( TokenTypes::T_SECTION_OPEN , '{' ) )
            $endDecider = 'decideIfBraceEnd';

        $stream->expect(TokenTypes::T_SECTION_OPEN);
        $body = $parser->subparse([$this,  $endDecider ]);

        if ( $endDecider == 'decideIfEnd' ) {
            $stream->expect(TokenTypes::T_SECTION_TYPE, 'endblock');
            $stream->expect(TokenTypes::T_SECTION_OPEN);
        } else {
            $stream->expect( TokenTypes::T_SECTION_CLOSE , '}' );
        }

        $parser->setBlock( $blockName->getValue() ,  new Node\Block( $body , $blockName->getValue() , $line ) );

        return new Node\PrintNode( new Node\RenderBlock( $blockName->getValue() , new Node\Expression\Arguments() , $line ) , $line );

    }

    public function decideIfEnd( TokenStream $stream ) {
        return $stream->nextIf( [ TokenTypes::T_SECTION_TYPE ] , 'endblock' );
    }

    public function decideIfBraceEnd( TokenStream $stream ) {
        return $stream->test( [ TokenTypes::T_SECTION_CLOSE ] , '}' );
    }
}