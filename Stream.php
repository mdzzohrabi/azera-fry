<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry;

/**
 * Class Stream
 *
 * @package Azera\Fry
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class Stream
{

    /**
     * @var array
     */
    protected $items;

    /**
     * @var int
     */
    protected $offset = 0;

    public function __construct( array $items = [] )
    {
        $this->items = $items;
    }

    public function isEOF() {
        return $this->offset >= $this->size();
    }

    public function size() {
        return count($this->items);
    }

    public function getOffset( $offset ) {
        if ( !isset( $this->items[ $offset ] ) ) {
            throw new \Exception(sprintf('Stream out of range offset %s', $offset));
        }
        return $this->items[$offset];
    }

    public function getCurrent() {
        return $this->getOffset( $this->offset );
    }

    public function next() {
        if ( !isset( $this->items[ ++$this->offset ] ) )
            throw new \Exception(sprintf('Stream out of range offset %s', $this->offset ));
        return $this->items[ $this->offset - 1 ];
    }

    public function prev() {
        return $this->getOffset( --$this->offset );
    }

}