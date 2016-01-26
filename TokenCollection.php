<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry;

/**
 * Class TokenCollection
 *
 * @package Azera\Fry
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class TokenCollection implements \ArrayAccess, \Countable
{

    /**
     * @var
     */
    private $fileName;

    public function __construct( $fileName )
    {
        $this->fileName = $fileName;
    }

    /**
     * Tokens
     *
     * @var array|Token[]
     */
    protected $tokens = array();

    /**
     * Add token
     *
     * @param Token $token
     */
    public function add( Token $token ) {
        $this->tokens[] = $token;
    }

    /**
     * Get tokens as array
     *
     * @return array|Token[]
     */
    public function getTokens() {
        return $this->tokens;
    }

    /**
     * @return Token
     */
    public function last() {
       return $this->tokens[ count($this->tokens) - 1 ];
    }

    /**
     * @return TokenStream
     */
    public function getStream() {
        return new TokenStream( $this->tokens , $this->fileName );
    }

    protected function offset( $offset ) {
        return $offset == '' && $offset === null ? count( $this->tokens ) : $offset;
    }

    public function offsetExists($offset)
    {
        return isset( $this->tokens[ $this->offset( $offset ) ] );
    }

    public function offsetGet($offset)
    {
        return $this->tokens[ $this->offset( $offset ) ];
    }

    public function offsetSet($offset, $value)
    {
        $this->tokens[ $this->offset( $offset ) ] = $value;
    }

    public function offsetUnset($offset)
    {
        unset( $this->tokens[ $this->offset($offset) ] );
    }

    public function count()
    {
        return count( $this->tokens );
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

}