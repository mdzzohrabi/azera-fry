<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Node\Expression;

use Azera\Fry\Compiler;

class Range extends Binary
{

    public function compile(Compiler $compiler)
    {

        $compiler
            ->raw('range(')
            ->subcompile( $this->getNode(self::NODE_1) )
            ->raw( ', ' )
            ->subcompile( $this->getNode(self::NODE_2) )
            ->raw(')')
        ;

    }

}