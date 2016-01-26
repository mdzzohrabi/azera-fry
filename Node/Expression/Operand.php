<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Node\Expression;

use Azera\Fry\Node;

class Operand extends Node
{

    /**
     * Operand constructor.
     *
     * @param array|\Azera\Fry\Node[] $nodes
     * @param array                   $operators
     * @param int                     $lineNo
     */
    public function __construct($nodes, $operators, $lineNo)
    {
        parent::__construct($nodes, [
            'operators' => $operators
        ], $lineNo);
    }

}