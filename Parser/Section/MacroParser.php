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
use Azera\Fry\TokenTypes;

/**
 * Class MacroParser
 * @package Azera\Fry\Parser\Section
 */
class MacroParser extends AbstractSectionParser
{

    const SECTION_START = 'macro';
    const SECTION_END = 'endmacro';

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return Node
     */
    public function parse(Token $token, Parser $parser)
    {

        parent::parse( $token , $parser );

        $stream = $parser->getStream();

        $name = $stream->expect( TokenTypes::T_NAME , null , 'Missing macro name' );
        $args = null;

        if ( $stream->test( TokenTypes::T_OPEN_PARAN ) ) {
            $args = $parser->getExpressionParser()->parseArguments( false , true );
        }

        $body = $this->parseBody( $parser );

        $parser->setMacro(
            $name->getValue() ,
            new Node\Macro( $name->getValue() , $args , $body , $token->getLine() )
        );

    }

    /**
     * @return string
     */
    public function getSectionName()
    {
        return self::SECTION_START;
    }

    /**
     * @return int
     */
    public function getStartTokenType()
    {
        return TokenTypes::T_SECTION_TYPE;
    }

    /**
     * @return string
     */
    public function getSectionEnd()
    {
        return self::SECTION_END;
    }

    /**
     * @return boolean
     */
    public function allowBrace()
    {
        return true;
    }
}