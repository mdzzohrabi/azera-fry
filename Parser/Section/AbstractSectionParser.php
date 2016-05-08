<?php
/**
 * Created by PhpStorm.
 * User: Masoud
 * Date: 18/04/2016
 * Time: 05:07 PM
 */

namespace Azera\Fry\Parser\Section;

use Azera\Fry\Exception\Exception;
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

    const END_TYPE_BRACE = 0;
    const END_TYPE_NAME = 1;
    const END_TYPE_BOTH = 2;

    protected $endType;

    public function __construct()
    {

        if ( $this->allowBrace() && !!$this->getSectionEnd() )
            $this->endType = self::END_TYPE_BOTH;
        elseif ( $this->allowBrace() )
            $this->endType = self::END_TYPE_BRACE;
        elseif ( !!$this->getSectionEnd() )
            $this->endType = self::END_TYPE_NAME;
        else
            throw new Exception('Invalid section %s end type.' , $this->getSectionName() );

    }

    public function getStartTokenType() {
        return null;
    }

    /**
     * @param Token $token
     * @param Parser $parser
     * @return bool
     */
    public function canParse(Token $token, Parser $parser)
    {
        return $token->test( $this->getStartTokenType() , [ $this->getSectionName() ] );
    }


    public function parse(Token $token, Parser $parser)
    {

        $stream = $parser->getStream();

        $stream->expect( null, [ $this->getSectionName() ] , sprintf('Unexpected token, %s expected' , $this->getSectionName() ) );

    }

    /**
     * @param Parser $parser
     * @return \Azera\Fry\Node
     * @throws \Azera\Fry\Exception\Exception
     */
    public function parseBody( Parser $parser ) {

        $stream = $parser->getStream();

        $isBrace = false;

        if ( $this->endType() == self::END_TYPE_BRACE ) {
            $isBrace = !!$stream->expect(TokenTypes::T_SECTION_OPEN, '{');
        } elseif ( $this->endType() == self::END_TYPE_BOTH && $stream->test( TokenTypes::T_SECTION_OPEN , '{' ) ) {
            $isBrace = !!$stream->next();
        } else {
            $stream->expect( TokenTypes::T_SECTION_OPEN );
        }

        $body = $parser->subparse([ $this , $isBrace ? 'testEndBrace' : 'testEnd' ]);

        if ( $isBrace ) {
            $stream->expect( TokenTypes::T_SECTION_CLOSE , '}' );
        } else {
            $stream->expect(TokenTypes::T_SECTION_TYPE, $this->getSectionEnd());
            $stream->expect(TokenTypes::T_SECTION_OPEN);
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

    public function testEndBrace( TokenStream $stream ) {
        return $stream->test( TokenTypes::T_SECTION_CLOSE , '}' );
    }

    protected function endType() {
        return $this->endType;
    }


}