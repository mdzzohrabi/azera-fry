<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Core;


class Filter
{

    /** @var string */
    protected $name;

    /** @var callable */
    protected $callable;

    /** @var bool  */
    protected $safe = false;

    /** @var bool  */
    protected $injectEnvironment = false;

    public function __construct( $name , $callable , $safe = false , $injectEnvironment = false )
    {
        $this->name = $name;
        $this->callable = $callable;
        $this->safe = $safe;
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
        return $this->safe;
    }

    /**
     * @return boolean
     */
    public function isInjectEnvironment()
    {
        return $this->injectEnvironment;
    }

}