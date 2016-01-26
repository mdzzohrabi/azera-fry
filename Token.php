<?php
namespace Azera\Fry;

use Azera\Core\Collection;

class Token {

	/**
	 * Token string
	 * @var string
	 */
	protected $name;

	/**
	 * Token type
	 * @var integer
	 */
	protected $tokenType;

	/**
	 * Token name
	 * @debug
	 * @var string
	 */
	protected $tokenName;

	/**
	 * Token line
	 * @var integer
	 */
	protected $line;

	/**
	 * Token column
	 * @var integer
	 */
	protected $column;

	/**
	 * Tokens Collection
	 * @var Collection
	 */
	protected $list;

	/**
	 * Index of token in token collection
	 * @var integer
	 */
	protected $index;


	/**
	 * Token constructor.
	 *
	 * @param TokenCollection $list 		Token Collection
	 * @param string          $name 		Token content
	 * @param int          	  $tokenType	Token type
	 * @param int             $line			Token line
	 * @param int             $column		Token column offset
	 */
	public function __construct( TokenCollection $list = null , $name , $tokenType , $line , $column ) {

		$this->name = $name;
		$this->tokenType = $tokenType;
		$this->tokenName = TokenTypes::getName( $tokenType );
		$this->line = $line;
		$this->column = $column;
		$this->list = $list;
		$this->index = count( $list );

	}

	/**
	 * Check token type
	 *
	 * @param  integer  $tokenType Token type
	 * @return boolean
	 */
	public function is( $tokenType ) {
		return $this->tokenType == $tokenType;
	}

    /**
     * @param int[]|int $tokenType
     * @param string|string[] $value
     * @return bool
     */
    public function test( $tokenType , $value = null ) {

        $tokenTypes = (array)$tokenType;
        $values     = (array)$value;
        return ( is_null($tokenType) || in_array( $this->tokenType , $tokenTypes ) ) && ( is_null($value) || in_array( $this->name , $values ) );

    }

	/**
	 * Check if next token exists
	 *
	 * @return boolean
	 */
	public function hasNext() {
		return isset( $this->list[ $this->index + 1 ] );
	}

	/**
	 * Get token name
	 *
	 * @return string  Token name
	 */
	public function getName() {
		return $this->name;
	}

    /**
     * @return string
     */
    public function getValue() {
        return $this->name;
    }

	/**
	 * Token type
	 *
	 * @return integer 	Token type
	 */
	public function getType() {
		return $this->tokenType;
	}

	/**
	 * Get token index
	 *
	 * @return integer
	 */
	public function getIndex() {
		return $this->index;
	}

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return int
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return string
     */
    public function getTokenName()
    {
        return $this->tokenName;
    }

}
?>
