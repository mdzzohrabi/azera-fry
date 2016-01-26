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

class Binary extends Node
{

    const ATTR_OPERATOR = 'operator';
    const NODE_1 = 'node_1';
    const NODE_2 = 'node_2';

    /**
     * Binary constructor.
     *
     * @param Node    $node_1
     * @param Node    $node_2
     * @param string  $operator
     * @param int     $lineNo
     */
    public function __construct( $node_1 , $node_2 , $operator , $lineNo)
    {
        parent::__construct([ self::NODE_1 => $node_1 , self::NODE_2 => $node_2 ], [ self::ATTR_OPERATOR => $operator ], $lineNo);
    }

    public function compile(Compiler $compiler)
    {

        $operator = $this->getAttribute(self::ATTR_OPERATOR);

        $cast = [
            '~' => '.',
            'equals' => '==',
            'greater than' => '>=',
            'is' => '==='
        ];

        if ( isset($cast[ $operator ]) )
            $operator = $cast[ $operator ];

        $this->getNode(self::NODE_1)->compile( $compiler );

        $compiler->raw(" $operator ");

        $this->getNode(self::NODE_2)->compile( $compiler );

    }

}