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

class PrintNode extends Node
{

    const NODE_EXPRESSION = 'expression';

    /**
     * PrintNode constructor.
     *
     * @param Node  $expr
     * @param int   $lineNo
     */
    public function __construct( Node $expr , $lineNo)
    {
        parent::__construct([ self::NODE_EXPRESSION => $expr ], [], $lineNo);
    }

    public function compile(Compiler $compiler)
    {

        $compiler
            ->write('echo ');

        $this->getNode(self::NODE_EXPRESSION)->compile( $compiler );

        $compiler
            ->raw(';')
            ->line()
        ;

    }

}