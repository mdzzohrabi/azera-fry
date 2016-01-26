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
 * Class Arguments
 *
 * @package Azera\Fry\Node\Expression
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class Arguments extends Node
{


    const ATTR_DEFINITION = 'definition';

    /**
     * Arguments constructor.
     *
     * @param array     $args
     * @param bool      $definition
     * @param int       $lineNo
     */
    public function __construct( array $args = [] , $definition = false , $lineNo = null )
    {
        parent::__construct($args,[
            self::ATTR_DEFINITION => $definition
        ], $lineNo);
    }

    public function size() {
        return count( $this->nodes );
    }

    public function getArgument( $index ) {
        return $this->getNode( $index );
    }

    public function compile(Compiler $compiler)
    {

        $def = $this->getAttribute( self::ATTR_DEFINITION );

        if ( $def )
            $this->compileDefinition( $compiler );
        else
            $this->compileArguments( $compiler );


    }

    private function compileDefinition( Compiler $compiler ) {

        foreach ( $this->nodes as $name => $value ) {

            $compiler->raw( '$' . $name );

            if ( $value ) {
                $compiler
                    ->raw(' = ')
                    ->subcompile( $value );
            }

        }

    }

    private function compileArguments( Compiler $compiler ) {

        $compiler
            ->raw('[');

        $first = true;

        foreach ( $this->nodes as $name => $node ) {

            if ( !$first )
                $compiler->raw(',');

            if ( is_string($name) )
                $compiler
                    ->string( $name )
                    ->raw(' => ');

            $node->compile( $compiler );

            $first = false;

        }

        $compiler->raw(']');

    }

}