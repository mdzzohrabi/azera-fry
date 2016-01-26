<?php
namespace Azera\Fry;

/**
 * Text Reader
 * Azera Fry Template engine
 *
 * @author Masoud Zohrabi <mdzzohrabi@mgail.com>
 * @package Azera\Fry
 */
class Reader {

	protected $fileName;

	/**
	 * Text
	 * @var string
	 */
	protected $input;

	/**
	 * Cursor poistion
	 * @var integer
	 */
	protected $cursor = 0;

	/**
	 * Text length
	 * @var integer
	 */
	protected $length;


	/**
	 * Constructor
	 *
	 * @param  string $input Input text
	 * @param         $fileName
	 */
	function __construct( $input , $fileName = null ) {

		$this->input = str_replace( ["\r\n", "\r"] , "\n" , $input );
		$this->length = strlen( $input );
		$this->fileName = $fileName;
	}

	/**
	 * Read characters
	 *
	 * @method read
	 * @param  integer $length Length
	 * @return string          Characters
	 */
	public function read( $length = 1 ) {

		# Read text from begin of cursor by length
		return substr( $this->readToEnd() , 0 , $length );

	}

	/**
	 * @param      $offset
	 * @param null $length
	 * @return string
	 */
	public function readFromStart( $offset , $length = null ) {

		if ( $length )
			return substr( $this->input , $offset , $length );
		return substr( $this->input , $offset );

	}

	/**
	 * Read text and move cursor by length
	 *
	 * @param  integer   $length Length of characters
	 * @return string            Text
	 */
	public function readAndGo( $length = 1 ) {

		# Read text from begin of cursor by length
		$text = $this->read( $length );

		# Set cursor position
		$this->moveCursor( $length );

		return $text;

	}

	/**
	 * Read text from cursor position
	 *
	 * @return string    Text
	 */
	public function readToEnd() {
		return substr( $this->input , $this->cursor );
	}

	/**
	 * Read text from cursor position at set cursor to end
	 *
	 * @return string
	 */
	public function readToEndAndGo() {
		$text = $this->readToEnd();
		$this->setCursor( $this->length - 1 );
		return $text;
	}

	/**
	 * Match regular expression and return matched keyword
	 *
	 * @param  string $regexp Regular expression
	 * @return bool|string         Matched word
	 */
	public function match( $regexp ) {

		# Regular expression matching
		if ( preg_match( $regexp , $this->readToEnd() , $match ) )
			return current( $match );

		return false;

	}

	public function strippedMatch( $regex ) {

        if ( $result = $this->match($regex) ) return $result;

		$regex = '/(?:\s*)' . substr($regex,1);

		return $this->match( $regex );

	}

    public function strippedMatchAndGo( $regex ) {

        $regex = '/(\s*)' . substr($regex,1);

        if ( ($match = $this->match( $regex )) === false ) return false;

        $this->moveCursor( strlen($match) );

        return ltrim( $match , ' ' );

    }

	/**
	 * Match regular expression, move cursor and return matched keyword
	 *
	 * @param  string $regexp Regular expression
	 * @return bool|string         Matched word
	 */
	public function matchAndGo( $regexp ) {

		if ( ($match = $this->match( $regexp )) === false ) return false;

		$this->moveCursor( strlen( $match ) );

		return $match;

	}

	/**
	 * Check if cursor is at the end of text
	 *
	 * @return boolean
	 */
	public function isEnd() {
		return $this->cursor >= ($this->length - 1);
	}

	/**
	 * Return original text length
	 *
	 * @return integer
	 */
	public function length() {
		return $this->length;
	}

	/**
	 * Count remaining characters
	 *
	 * @return int
	 */
	public function count() {
		return strlen ( $this->readToEnd() );
	}

	/**
	 * Get current position line number

	 * @return integer 		Line number
	 */
	public function getLine() {
		return count( explode( "\n" , substr( $this->input , 0 , $this->cursor ) ) );
	}

	/**
	 * Get current position column number
	 *
	 * @return integer 		Column number
	 */
	public function getColumn() {
		return strlen( end( explode( "\n" , substr( $this->input , 0 , $this->cursor ) ) ) );
	}

	/**
	 * Get cursor position
	 *
	 * @return integer
	 */
	public function getCursor() {
		return $this->cursor;
	}

	/**
	 * Set cursor position
	 *
	 * @param  integer    $cursor Cursor position
	 * @return Reader
	 */
	public function setCursor( $cursor ) {
		$this->cursor = $cursor;
		return $this;
	}

	/**
	 * Move cursor by count
	 *
	 * @param  integer    $cursor Count
	 * @return Reader
	 */
	public function moveCursor( $cursor = 1 ) {
		$this->cursor += $cursor;
		return $this;
	}

	/**
	 * Rewind cursor position
	 *
	 * @return Reader
	 */
	public function rewind() {
		$this->cursor = 0;
		return $this;
	}

	/**
	 * String casting overload. return original text
	 *
	 * @return string 		Original text
	 */
	public function __toString() {
		return $this->input;
	}

	/**
	 * Return original input
	 *
	 * @return string
	 */
	public function readAll() {
		return $this->input;
	}

	/**
	 * @return mixed
	 */
	public function getFileName()
	{
		return $this->fileName;
	}

}
?>
