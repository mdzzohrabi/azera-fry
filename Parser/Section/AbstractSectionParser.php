<?php
/**
 * Created by PhpStorm.
 * User: Masoud
 * Date: 18/04/2016
 * Time: 05:07 PM
 */

namespace Azera\Fry\Parser\Section;

use Azera\Fry\Parser;
use Azera\Fry\Token;
use Azera\Fry\TokenStream;
use Azera\Fry\TokenTypes;

/**
 * Class AbstractSectionParser
 * @package Azera\Fry\Parser\Section
 */
abstract class AbstractSectionParser implements SectionParserInterface
{

    /**
     * @param Token $token
     * @param Parser $parser
     * @return bool
     */
    public function canParse(Token $token, Parser $parser)
    {
        return $token->test( null , [ $this->getSectionName() ] );
    }


    public function parse(Token $token, Parser $parser)
    {

        $stream = $parser->getStream();

        $stream->expect( null, [ $this->getSectionName() ] , sprintf('Unexpected token, %s expected' , $this->getSectionName() ) );
        $stream->expect( TokenTypes::T_SECTION_OPEN );

    }

    /**
     * @param Parser $parser
     * @return \Azera\Fry\Node
     * @throws \Azera\Fry\Exception\Exception
     */
    public function parseBody( Parser $parser ) {
        $body = $parser->subparse([ $this , 'testEnd' ]);
        if ( $this->getSectionEnd() != '' ) {
            $parser->getStream()->expect(null, $this->getSectionEnd());
            $parser->getStream()->expect(TokenTypes::T_SECTION_OPEN);
        }
        return $body;
    }

    /**
     * @param TokenStream $stream
     * @return bool
     */
    public function testEnd( TokenStream $stream ) {
        return $stream->nextIf( [ TokenTypes::T_SECTION_TYPE ], $this->getSectionEnd() );
    }

}