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

/**
 * Interface ParserInterface
 *
 * @package Azera\Fry\Parser
 */
interface ParserInterface
{

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return bool
     */
    public function canParse( Token $token , Parser $parser );

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return Node
     */
    public function parse( Token $token , Parser $parser );

}