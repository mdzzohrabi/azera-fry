<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Node\Expression;


use Azera\Fry\Compiler;
use Azera\Fry\Node;

class Constant extends Node
{

    /**
     * Constant constructor.
     *
     * @param string     $constant
     * @param int        $lineNo
     */
    public function __construct( $constant , $lineNo)
    {
        parent::__construct([], [ 'constant'  => $constant ], $lineNo);
    }

    public function compile(Compiler $compiler)
    {

        $compiler->raw( $this->getAttribute( 'constant' ) );

    }

}