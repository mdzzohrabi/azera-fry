<?php
/**
 * Created by PhpStorm.
 * User: Masoud
 * Date: 18/04/2016
 * Time: 03:19 PM
 */

namespace Azera\Fry\Extension\Core;

use Azera\Fry\Node;
use Azera\Fry\Parser;
use Azera\Fry\Token;

class SandboxParser extends Parser\Section\AbstractSectionParser
{

    const SANDBOX = 'sandbox';
    const END_SANDBOX = 'endsandbox';

    /**
     * @param Token $token
     * @param Parser $parser
     * @return Node
     */
    public function parse(Token $token, Parser $parser)
    {

        parent::parse( $token , $parser );

        return new SandboxNode( $this->parseBody( $parser ) , $token->getLine() );

    }

    /**
     * @return string
     */
    public function getSectionName()
    {
        return self::SANDBOX;
    }

    /**
     * @return string
     */
    public function getSectionEnd()
    {
        return self::END_SANDBOX;
    }
}