<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Core;


class Operator
{

    const OPERATOR_LEFT = 1;
    const OPERATOR_RIGHT = 2;
    const DEFAULT_CLASS = 'Azera\Fry\Node\Expression\Binary';

    /** @var  string */
    private $name;

    /** @var  int */
    private $precedence;

    /** @var  string */
    private $class;

    /** @var int */
    private $associativity;

    public function __construct( $name , $precedence , $class = self::DEFAULT_CLASS , $associativity = self::OPERATOR_LEFT )
    {
        $this->name = $name;
        $this->precedence = $precedence;
        $this->class = $class;
        $this->associativity = $associativity;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getPrecedence()
    {
        return $this->precedence;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return int
     */
    public function getAssociativity()
    {
        return $this->associativity;
    }

}