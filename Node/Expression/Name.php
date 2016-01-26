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

class Name extends Node
{

    const ATTR_NAME = 'name';

    /**
     * Name constructor.
     *
     * @param string $name
     * @param int    $lineNo
     */
    public function __construct( $name , $lineNo)
    {
        parent::__construct([], [ self::ATTR_NAME    => $name ], $lineNo);
    }

    public function getName() {
        return $this->getAttribute( self::ATTR_NAME );
    }

    protected function convertToRoute( $name ) {

        preg_match_all( '/[a-z0-9_]+/i' , $name , $finds );
        return current($finds);

    }

    public function compile(Compiler $compiler)
    {

        $name = $this->getAttribute( self::ATTR_NAME );

        $route = $this->convertToRoute( $name );

        $compiler
            ->raw( '$this->getValue( $context , ["' . implode( '","' , $route ) . '"] )' )
            ;

    }

}