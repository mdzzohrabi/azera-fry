<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Node\Expression;

use Azera\Core\NativeFunction;
use Azera\Fry\Compiler;
use Azera\Fry\Node;

class Call extends Node
{

    const ATTR_NAME = 'name';
    const NODE_ARGUMENTS = 'args';

    /**
     * Call constructor.
     *
     * @param string    $name
     * @param Node      $arguments
     * @param int       $lineNo
     */
    public function __construct( $name , Node $arguments , $lineNo)
    {
        parent::__construct([
            self::NODE_ARGUMENTS    => $arguments
        ], [
            self::ATTR_NAME => $name
        ], $lineNo);
    }

    protected function convertToRoute( $name ) {

        preg_match_all( '/[a-z0-9_]+/i' , $name , $finds );
        return current($finds);

    }

    public function compile(Compiler $compiler)
    {
        $env = $compiler->getEnvironment();

        $functionName = $this->getAttribute( self::ATTR_NAME );
        /** @var Arguments $args */
        $args         = $this->getNode( self::NODE_ARGUMENTS );

        if ( $env->hasFunction( $functionName ) && ( $func = $env->getFunction( $functionName ) ) instanceof NativeFunction ) {
            $func->compile( $compiler , $args );
            return;
        }

        if ( $functionName == 'renderBlock' ) {

            if ( $args->size() > 2 )
                throw new \LogicException(sprintf( 'Invalid renderBlock arguments count , %d , %d expected' , $args->size() , 2 ));

            if ( $args->size() < 1 )
                throw new \LogicException(sprintf( 'Invalid renderBlock, block name not defined'));

            if ( $args->size() == 2 && !$args->getNode( 1 ) instanceof ArrayNode )
                throw new \LogicException(sprintf( 'Invalid renderBlock, context must be array'));

            $compiler
                ->raw('$this->renderBlock(')
                ->subcompile( $args->getArgument( 0 ) )
                ->raw(',');

            if ( $args->size() == 2 ) {
                $compiler
                    ->raw('array_merge( $context , ')
                    ->subcompile( $args->getArgument( 1 ) )
                    ->raw(')')
                ;
            } else {
                $compiler->raw(' $context ');
            }

            if ( $args )

            $compiler
                ->raw(')');

            return;
        }

        $compiler
            ->raw('$this->getValue( $context , ')
            ->raw( '["' . implode( '","' , $this->convertToRoute( $this->getAttribute(self::ATTR_NAME) ) ) . '"]' )
            ->raw(' , self::METHOD_CALL , ')
            ->subcompile( $this->getNode(self::NODE_ARGUMENTS) )
            ->raw(')')
        ;

    }

}