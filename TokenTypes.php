<?php
namespace Azera\Fry;

class TokenTypes {

	const T_STR = 0;
	const T_NUM = 1;

	const T_OPEN_BRACE = 2;
	const T_CLOSE_BRACE = 3;

	const T_OPEN_PARAN = 4;
	const T_CLOSE_PARAN = 5;

	const T_RANGE = 6;
	const T_KEYWORD = 9;
	const T_BOOLEAN = 10;
	const T_OPEN_BRACKET = 11;
	const T_CLOSE_BRACKET = 12;
	const T_END_LINE = 13;
	const T_END_CODE = 14;
	const T_START = 15;
	const T_COLON = 16;
	const T_SEMICOLON = 17;
	const T_COMMA = 18;
	const T_OPERATOR = 19;
	const T_NAME = 21;
	const T_RAW = 24;
	const T_CODE_BLOCK = 25;

	const T_BLOCK_PRINT_OPEN = 29;
	const T_BLOCK_PRINT_CLOSE = 30;

	const T_BLOCK_OPEN = 31;
	const T_BLOCK_CLOSE = 32;

	const T_FILTER = 34;

	const T_SECTION_OPEN = 0x0034;
	const T_SECTION_CLOSE = 0x0035;
	const T_SECTION_START = 0x1036;
	const T_SECTION_TYPE = 0x0037;

	const T_SET 	= 0x0038;
	const T_EOF = 0x0039;
	const T_NULL = 0x0040;

	const T_COMMENT_START = 0x0041;
	const T_COMMENT_END = 0x0042;
	const T_COMMENT = 0x0043;

	/** @deprecated */
	const T_COMPARE_OPERATOR = 0x0036;
	/** @deprecated */
	const T_MARKUP_BLOCK = 26;
	/** @deprecated */
	const T_FUNC = 7;
	/** @deprecated */
	const T_VAR = 8;
	/** @deprecated */
	const T_CONST = 20;
	/** @deprecated */
	const T_VBAR = 22;
	/** @deprecated */
	const T_CLASS_SCOPE = 23;
	/** @deprecated */
	const T_BLOCK_CLOSURE_OPEN = 27;
	/** @deprecated */
	const T_BLOCK_CLOSURE_CLOSE = 28;


	/**
	 * Get token name
	 * @param $tokenType
	 * @return bool|int|string
	 */
	public static function getName( $tokenType ) {
		$ref = new \ReflectionClass( static::class );
		foreach ( $ref->getConstants() as $constant => $value ) {
			if ( $value == $tokenType ) return $constant;
		}
		return false;
	}

}
?>
