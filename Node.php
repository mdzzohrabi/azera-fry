<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry;

/**
 * Class Node
 *
 * @package Azera\Fry\Parser
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class Node
{

    /**
     * Node attributes
     * @var array
     */
    protected $attributes = [];

    /**
     * Named nodes
     * @var Node[]
     */
    protected $nodes = [];

    /**
     * Node line number
     * @var int
     */
    protected $lineNo;

    /**
     * Node constructor.
     *
     * @param Node[] $nodes
     * @param array  $attributes
     * @param int    $lineNo
     */
    public function __construct( array $nodes = [] , array $attributes = [] , $lineNo = 0 )
    {
        $this->nodes = $nodes;
        $this->attributes = $attributes;
        $this->lineNo = $lineNo;
    }

    public function compile( Compiler $compiler ) {
        foreach ( $this->nodes as $node )
            $node->compile( $compiler );
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getAttribute( $name ) {
        if ( !isset( $this->attributes[ $name ] ) )
            throw new \LogicException(sprintf('Attribute "%s" does not exists for node "%s"', $name , get_class($this) ));
        return $this->attributes[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasAttribute( $name ) {
        return isset( $this->attributes[$name] );
    }

    /**
     * @param $name
     * @return Node
     */
    public function getNode( $name ) {
        if ( !isset( $this->nodes[ $name ] ) )
            throw new \LogicException(sprintf('Node "%s" does not exists for node "%s"', $name , get_class($this) ));
        return $this->nodes[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasNode( $name ) {
        return isset( $this->nodes[$name] );
    }

    public function hasChild() {
        return !empty($this->nodes);
    }

    /**
     * @return int
     */
    public function getLineNo()
    {
        return $this->lineNo;
    }

    /**
     * @return Node[]
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }


}