<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Parser;

use Azera\Fry\Node;
use Azera\Fry\Parser;
use Azera\Fry\Token;
use Azera\Fry\TokenTypes;

class PrintParser implements ParserInterface
{

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return bool
     */
    public function canParse(Token $token, Parser $parser)
    {
        return $token->test([ TokenTypes::T_BLOCK_PRINT_OPEN ]);
    }

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return Node
     */
    public function parse(Token $token, Parser $parser)
    {

        $stream = $parser->getStream();
        $stream->expect( TokenTypes::T_BLOCK_PRINT_OPEN );

        $expr = $parser->parseExpression();

        $stream->expect( TokenTypes::T_BLOCK_PRINT_CLOSE );

        return new Node\PrintNode( $expr , $token->getLine() );

    }
}