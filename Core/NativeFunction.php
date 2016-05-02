<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Core;

use Azera\Fry\Compiler;
use Azera\Fry\Node;

/**
 * Class NativeFunction
 *
 * @package Azera\Core
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class NativeFunction
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Callable
     */
    protected $compile;

    public function __construct( $name , $compile = null )
    {
        $this->name = $name;
        $this->compile = $compile;
    }

    /**
     * @param Compiler $compiler
     * @param Node     $arguments
     */
    public function compile( Compiler $compiler , Node $arguments ) {
        $this->compile( $compiler , $arguments );
    }

}