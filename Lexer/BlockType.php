<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Lexer;


class BlockType
{

    const BLOCK_PRINT = 0x01;
    const BLOCK_BLOCK = 0x02;
    const BLOCK_CLOSURE = 0x03;
    const BLOCK_SECTION = 0x04;
    const BLOCK_COMMENT = 0x05;
    const BLOCK_COMMENT_SINGLE = 0x06;

}