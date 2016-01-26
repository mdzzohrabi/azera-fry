<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Parser;

use Azera\Fry\Node;
use Azera\Fry\Node\CommentNode;
use Azera\Fry\Parser;
use Azera\Fry\Token;
use Azera\Fry\TokenTypes;

/**
 * Class CommentParser
 *
 * @package Azera\Fry\Parser
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class CommentParser implements ParserInterface
{

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return bool
     */
    public function canParse(Token $token, Parser $parser)
    {
        return $token->test( TokenTypes::T_COMMENT_START );
    }

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return Node
     */
    public function parse(Token $token, Parser $parser)
    {

        $stream = $parser->getStream();

        $stream->expect( TokenTypes::T_COMMENT_START );

        $comment = $stream->expect( TokenTypes::T_COMMENT );

        $stream->expect( TokenTypes::T_COMMENT_END );

        return new CommentNode( $comment->getValue() , $token->getLine() );

    }
}