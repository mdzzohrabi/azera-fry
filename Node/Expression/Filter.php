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

/**
 * Class Filter
 *
 * @package Azera\Fry\Node\Expression
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class Filter extends Node
{

    const NODE_CONTENT = 'node';
    const NODE_NAME    = 'name';
    const NODE_ARGUMENTS = 'arguments';

    const ATTR_NAME     = 'name';

    /**
     * Filter constructor.
     *
     * @param Node      $node
     * @param string    $name
     * @param Node      $arguments
     * @param int       $lineNo
     */
    public function __construct( $node , $name , $arguments , $lineNo)
    {
        parent::__construct([
            self::NODE_CONTENT  => $node,
            self::NODE_ARGUMENTS => $arguments
        ], [
            self::ATTR_NAME     => $name
        ], $lineNo);
    }

    public function getArguments() {
        return $this->getNode(self::NODE_ARGUMENTS);
    }

    public function compile(Compiler $compiler)
    {

        $compiler
            ->raw('$this->filter(')
            ->string( $this->getAttribute(self::ATTR_NAME) )
            ->raw(', ')
        ;

        $this->getArguments()->compile($compiler);

        $compiler->raw(', function() use ( $context ){ return ');

        $this->getNode(self::NODE_CONTENT)->compile( $compiler );

        $compiler->raw(' ;}');

        $compiler->raw(')');
    }

}