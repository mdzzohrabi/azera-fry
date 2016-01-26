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
 * Class SetNode
 *
 * @package Azera\Fry\Node
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class SetNode extends Node
{

    const NODE_VALUE = 'value';
    const ATTR_VARIABLE = 'variable';
    const ATTR_IS_BLOCK = 'is_block';

    /**
     * SetNode constructor.
     *
     * @param string    $var
     * @param Node      $value
     * @param bool      $isBlock
     * @param int       $lineNo
     */
    public function __construct( $var , Node $value , $isBlock , $lineNo)
    {
        parent::__construct([
            self::NODE_VALUE => $value
        ], [
            self::ATTR_VARIABLE => $var,
            self::ATTR_IS_BLOCK => $isBlock
        ], $lineNo);
    }


    protected function convertToRoute( $name ) {

        preg_match_all( '/[a-z0-9_]+/i' , $name , $finds );
        return current($finds);

    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->write( '$this->set( $context , ' )
            ->raw( '["' . implode( '","', $this->convertToRoute( $this->getAttribute( self::ATTR_VARIABLE ) )) . '"]' )
            ->raw( ' , function() use ( $context ) {' . "\n" )
            ->indent()
        ;

        if ( $this->getAttribute( self::ATTR_IS_BLOCK ) ) {
            $compiler
                ->writeln('ob_start();')
                ->subcompile( $this->getNode( self::NODE_VALUE ) )
                ->writeln('return ob_get_clean();');
        } else {
            $compiler
                ->write('return ')
                ->subcompile( $this->getNode( self::NODE_VALUE ) )
                ->raw(';' . "\n")
            ;
        }

        $compiler
            ->outdent()
            ->writeln('});' . "\n");

    }

}