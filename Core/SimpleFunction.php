<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Core;


class SimpleFunction
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Callable
     */
    protected $callable;

    /**
     * @var bool
     */
    protected $isSafe = false;

    /**
     * @var bool
     */
    protected $injectEnvironment = false;

    public function __construct( $name , $callable , $isSafe = false , $injectEnvironment = false )
    {
        $this->name = $name;
        $this->callable = $callable;
        $this->isSafe = $isSafe;
        $this->injectEnvironment = $injectEnvironment;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * @return boolean
     */
    public function isSafe()
    {
        return $this->isSafe;
    }

    /**
     * @return boolean
     */
    public function isInjectEnvironment()
    {
        return $this->injectEnvironment;
    }

}