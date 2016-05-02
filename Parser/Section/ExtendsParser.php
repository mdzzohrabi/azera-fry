<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Parser\Section;


use Azera\Fry\Exception\Exception;
use Azera\Fry\Node;
use Azera\Fry\Parser;
use Azera\Fry\Parser\ParserInterface;
use Azera\Fry\Token;
use Azera\Fry\TokenTypes;

class ExtendsParser implements ParserInterface
{

    const EXTENDS_TAG = 'extends';

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return bool
     */
    public function canParse(Token $token, Parser $parser)
    {
        return $token->test( TokenTypes::T_SECTION_TYPE , self::EXTENDS_TAG );
    }

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return Node
     * @throws Exception
     * @throws \Azera\Fry\Exception\SyntaxException
     */
    public function parse(Token $token, Parser $parser)
    {

        $stream = $parser->getStream();

        $stream->expect( TokenTypes::T_SECTION_TYPE , self::EXTENDS_TAG );

        $parent = $parser->parseExpression();

        if ( !$parent instanceof Node\Expression\Constant || $parent->getType() !== Node\Expression\Constant::TYPE_STRING ) {
            throw new Exception( 'Invalid view layout value, layout must be a string. in line %d' , $token->getLine() );
        }

        $parser->setParent( $parent );

        $stream->expect( TokenTypes::T_SECTION_OPEN );

    }
}