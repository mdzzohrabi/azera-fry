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

class ArrayNode extends Node
{

    const NODE_ELEMENTS = 'elements';

    /**
     * ArrayNode constructor.
     *
     * @param array $elements
     * @param int   $lineNo
     */
    public function __construct( array $elements , $lineNo)
    {
        parent::__construct([
            self::NODE_ELEMENTS => $elements
        ], [], $lineNo);
    }

    public function compile(Compiler $compiler)
    {

        $compiler
            ->raw('array(');

        $elements = $this->getNode( self::NODE_ELEMENTS );

        $first = true;

        foreach (  $elements as $element ) {

            if ( !$first )
                $compiler->raw( ',' );

            list( $key , $value ) = $element;

            if ( $key ) {
                if ( $key instanceof Constant )
                    $compiler->subcompile( $key );
                elseif ( $key instanceof Name )
                    $compiler->string( $key->getName() );
                $compiler->raw( ' => ' );
            }

            $compiler->subcompile( $value );

            $first = false;

        }

        $compiler->raw(')');

    }

}