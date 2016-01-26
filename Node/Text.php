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

class Text extends Node
{

    /**
     * Text constructor.
     *
     * @param string    $text
     * @param int       $lineNo
     */
    public function __construct( $text , $lineNo )
    {
        parent::__construct([], [ 'text'    => $text ], $lineNo);
    }

    public function compile(Compiler $compiler)
    {

        $compiler
            ->write('echo ')
            ->string( $this->getAttribute('text') )
            ->raw(";\n");

    }

}