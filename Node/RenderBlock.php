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
 * Class RenderBlock
 *
 * @package Azera\Fry\Node
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class RenderBlock extends Node
{

    const NODE_CONTEXT = 'context';
    const ATTR_NAME = 'name';

    /**
     * RenderBlock constructor.
     *
     * @param string $blockName
     * @param Node   $context
     * @param int    $lineNo
     */
    public function __construct( $blockName , $context , $lineNo)
    {
        parent::__construct([
            self::NODE_CONTEXT => $context
        ], [
            self::ATTR_NAME => $blockName
        ], $lineNo);
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->raw( sprintf('$this->renderBlock("%s", ',$this->getAttribute( self::ATTR_NAME )) )
            ->raw('array_merge( $context , ')
            ->subcompile( $this->getNode( self::NODE_CONTEXT ) )
            ->raw('))')
        ;
    }

}