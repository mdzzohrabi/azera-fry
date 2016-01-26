<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Node;

use Azera\Fry\Compiler;
use Azera\Fry\Node;

/**
 * Class CommentNode
 *
 * @package Azera\Fry\Node
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class CommentNode extends Node
{
    const ATTR_COMMENT = 'comment';

    /**
     * CommentNode constructor.
     *
     * @param string $comment
     * @param int    $lineNo
     */
    public function __construct( $comment , $lineNo)
    {
        parent::__construct([], [
            self::ATTR_COMMENT => $comment
        ], $lineNo);
    }

    /**
     * @param Compiler $compiler
     */
    public function compile(Compiler $compiler)
    {
        $compiler
            ->writeln('/*')
            ->writeln( $this->getAttribute(self::ATTR_COMMENT) )
            ->writeln('*/');
    }

}