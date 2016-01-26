<?php
namespace Azera\Fry;

use Azera\Core\Collection;
use Azera\Fry\Exception\LexerException;
use Azera\Fry\Lexer\BlockType;
use Azera\Fry\Lexer\Breakpoint;

/**
 * Lexical Analyzer
 * Azera Fry Template Engine
 *
 * @author Masoud Zohrabi <mdzzohrabi@gmail.com>
 * @package Azera\Fry
 */
class Lexer {

	/**
	 * Tokens
	 * @var Token[]|TokenCollection
	 */
	protected $tokens;

//	/**
//	 * InputText
//	 * @var Reader
//	 * @deprecated
//	 */
//	protected $input;

	/**
	 * Input Text Reader
	 * @var Reader
	 */
	protected $reader;

    /**
     * Begin of all code blocks
     * @var string
     */
	protected $codeBlockRegex = '/(?<![\w\d])@/';

    /**
     * Section block names
     * @var array
     */
    protected $sectionKeywords = array(
        'using',
        'if',
        'elseif',
        'else',
        'endif',
        'block',
        'endblock',
        'macro',
        'for',
        'set'
    );

    /**
     * Blocks start and end definition
     * @var array
     */
    protected $codeBlocksRegex = array(
        BlockType::BLOCK_COMMENT  => [ '/\\/\\*/A' , '/\\*\\//A' ],
        BlockType::BLOCK_SECTION  => [ '/-/Ai' , '/\r?\n|$|\{/Am' ],
        BlockType::BLOCK_BLOCK    => [ '/\{/A' , '/\}/A' ],
//        BlockType::BLOCK_CLOSURE  => [ '/\(/A' , '/\)/A' ],
        BlockType::BLOCK_PRINT    => [ '/(?=\"|[a-z]|\()/Ai' , '/(?=.)|$/Ami' ],
    );

    /**
     * Block's start and end token names
     * @var array
     */
    protected $codeBlocksTokens = array(
        BlockType::BLOCK_COMMENT  => [ TokenTypes::T_COMMENT_START , TokenTypes::T_COMMENT_END ],
        BlockType::BLOCK_BLOCK    => [ TokenTypes::T_BLOCK_OPEN , TokenTypes::T_BLOCK_CLOSE ],
//        BlockType::BLOCK_CLOSURE  => [ TokenTypes::T_BLOCK_CLOSURE_OPEN , TokenTypes::T_BLOCK_CLOSURE_CLOSE ],
        BlockType::BLOCK_PRINT    => [ TokenTypes::T_BLOCK_PRINT_OPEN , TokenTypes::T_BLOCK_PRINT_CLOSE ],
        BlockType::BLOCK_SECTION  => [ TokenTypes::T_SECTION_START , TokenTypes::T_SECTION_OPEN ],
    );

    /**
     * Blocks
     * @var Callable[]
     */
    protected $blockTokenizer = array(
        BlockType::BLOCK_COMMENT  => 'tokenizeCommentBlock',
        BlockType::BLOCK_PRINT    => 'tokenizePrintBlock',
        BlockType::BLOCK_CLOSURE  => 'tokenizeClosureBlock',
        BlockType::BLOCK_BLOCK    => 'tokenizeBlock',
        BlockType::BLOCK_SECTION  => 'tokenizeSectionBlock',
    );

    /**
     * Tokens regular expression
     * @var array
     */
	protected $regex = array(

		TokenTypes::T_STR 		    => '/(\"|\')(.*?)(?<!\\\\)(\1)/As',
		TokenTypes::T_NUM 		    => '/[-+]?[0-9]+(\.[0-9]+)?/A',
		TokenTypes::T_KEYWORD 	    => '/print|if|else|(end|else)if|set|section|for|while|macro|in/A',
		TokenTypes::T_CODE_BLOCK    => '/@/A',
        TokenTypes::T_COLON         => '/:/A',
        TokenTypes::T_COMMA         => '/,/A',
        TokenTypes::T_SEMICOLON     => '/;/A',
        TokenTypes::T_RANGE         => '/[.]{2}/A',
        TokenTypes::T_OPEN_BRACE    => '/\{/A',
        TokenTypes::T_CLOSE_BRACE   => '/\}/A',
        TokenTypes::T_OPEN_BRACKET  => '/\[/A',
        TokenTypes::T_CLOSE_BRACKET => '/\]/A',
        TokenTypes::T_OPERATOR      => '/[-+]{2}|[-+\/*]|~|(<|>)=|={2}|<|>|\\.{2}|equals|match|has|is|not|more than|less than/A',
        TokenTypes::T_OPEN_PARAN    => '/\(/A',
        TokenTypes::T_CLOSE_PARAN   => '/\)/A',
        TokenTypes::T_FILTER        => '/\|/A',
        TokenTypes::T_BOOLEAN       => '/true|false/iA',
        TokenTypes::T_NULL          => '/null/iA',
        TokenTypes::T_SECTION_TYPE  => '//A',
//        TokenTypes::T_COMPARE_OPERATOR => '//iA',
        TokenTypes::T_SET           => '/=/A',
        TokenTypes::T_NAME 		    => "/([a-z_][_a-z0-9]*(\\.[_a-z0-9]+|\\[[_a-z0-9]+\\])*)/Ai" //'/[a-z_]+([_a-z0-9]+(\.)?)+/Ai'

	);

    /**
     * Expression tokens
     * @var array
     */
    protected $expressionBlockTokens = array(
        TokenTypes::T_KEYWORD,
        TokenTypes::T_NUM,
        TokenTypes::T_OPERATOR,
        TokenTypes::T_NULL,
        TokenTypes::T_BOOLEAN,
        TokenTypes::T_NAME,
        TokenTypes::T_STR,
        TokenTypes::T_OPEN_PARAN,
        TokenTypes::T_CLOSE_PARAN,
        TokenTypes::T_OPEN_BRACKET,
        TokenTypes::T_CLOSE_BRACKET,
        // [ Token , Must after ]
        [ TokenTypes::T_OPEN_BRACE , [ TokenTypes::T_OPEN_PARAN  , TokenTypes::T_SET , TokenTypes::T_COMMA , TokenTypes::T_KEYWORD , TokenTypes::T_COLON ] ],
        TokenTypes::T_CLOSE_BRACE,
        TokenTypes::T_FILTER,
//        TokenTypes::T_COMPARE_OPERATOR,
        TokenTypes::T_COLON,
        TokenTypes::T_RANGE,
        TokenTypes::T_COMMA,
        TokenTypes::T_SET
    );

    /**
     * Print block tokens
     * @var array
     */
    protected $printBlockTokens = array(
        TokenTypes::T_STR,
        TokenTypes::T_NAME
    );

	/**
	 * Estimated code block positions
     * @temp
	 * @var array
	 */
	protected $codeBlockPositions = array();

    /**
     * @var Breakpoint[]
     */
    protected $breakpoints = array();
    /**
     * @var Environment
     */
    private $environment;

    /**
     * @param Reader      $reader
     * @param Environment $environment
     */
	function __construct( Reader $reader , Environment $environment ) {

        # Parsed tokens collection
		$this->tokens = new TokenCollection( $reader->getFileName() );

        # Input reader
		$this->reader = $reader;

        # Section types
        $this->regex[ TokenTypes::T_SECTION_TYPE ] = '/' . implode('|',$this->sectionKeywords) . '/iA';

        # Print block tokens
        $this->printBlockTokens = array_intersect_key( $this->regex , array_flip( $this->printBlockTokens ) );

        # Expression tokens
        $_temp = [];
        foreach ( $this->expressionBlockTokens as $token ) {
            if ( is_array( $token ) )
                $_temp[ $token[0] ] = [ $this->regex[ $token[0] ] , $token[1] ];
            else
                $_temp[ $token ] = $this->regex[ $token ];
        }
        $this->expressionBlockTokens = $_temp;

        # Initialize Section block start token
        $this->codeBlocksRegex[ BlockType::BLOCK_SECTION ][0] = '/(?=' . implode('(\s|$)|',$this->sectionKeywords) . '(\s|$))/Ai';

        $this->environment = $environment;
    }

    /**
     * Get block end token
     *
     * @param $blockType
     * @return string
     */
    protected function getBlockEndToken( $blockType ) {
        return $this->codeBlocksRegex[ $blockType ][1];
    }

    /**
     * Get block begin token
     *
     * @param $blockType
     * @return string
     */
    protected function getBlockStartToken( $blockType ) {
        return $this->codeBlocksRegex[ $blockType ][0];
    }

    /**
     * @param $name
     * @param $type
     * @return Token
     */
    protected function createToken( $name , $type ) {
        return new Token(
            $this->tokens,
            $name,
            $type,
            $this->reader->getLine(),
            $this->reader->getColumn()
        );
    }

    /**
     * Push token to tokens collection
     *
     * @param $name
     * @param $type
     */
    protected function addToken( $name , $type ) {
        $this->tokens[] = $this->createToken( $name , $type );
    }

    /**
     * Is an open token ?
     *
     * @param $token
     * @return bool
     */
    protected function isOpenToken( $token ) {
        return in_array( $token , [
            TokenTypes::T_OPEN_PARAN,
            TokenTypes::T_OPEN_BRACE,
            TokenTypes::T_OPEN_BRACKET
        ]);
    }

    /**
     * Is a close token ?
     *
     * @param $token
     * @return bool
     */
    protected function isCloseToken( $token ) {
        return in_array( $token , [
            TokenTypes::T_CLOSE_PARAN,
            TokenTypes::T_CLOSE_BRACE,
            TokenTypes::T_CLOSE_BRACKET
        ]);
    }

    /**
     * Throw exception
     *
     * @param $message
     * @param ...$params
     * @throws LexerException
     */
    protected function throwException( $message , ...$params ) {
        $message .= ', at line %d column %d';
        $params[] = $this->reader->getLine();
        $params[] = $this->reader->getColumn();
        throw new LexerException( $message , ...$params );
    }

    /**
     * Get close token
     *
     * @param $token
     * @return mixed
     */
    protected function getCloseToken( $token ) {
        return [
            TokenTypes::T_OPEN_PARAN    => TokenTypes::T_CLOSE_PARAN,
            TokenTypes::T_OPEN_BRACKET  => TokenTypes::T_CLOSE_BRACKET,
            TokenTypes::T_OPEN_BRACE    => TokenTypes::T_CLOSE_BRACE
        ][ $token ];
    }

    /**
     * Get open token
     *
     * @param $token
     * @return mixed
     */
    protected function getOpenToken( $token ) {
        return [
            TokenTypes::T_CLOSE_PARAN   => TokenTypes::T_OPEN_PARAN,
            TokenTypes::T_CLOSE_BRACKET => TokenTypes::T_OPEN_BRACKET,
            TokenTypes::T_CLOSE_BRACE   => TokenTypes::T_OPEN_BRACE
        ][ $token ];
    }

    /**
     * If is open token , add close token to stack
     * If is close token and is granted return false
     * else returns false
     *
     * @param       $name
     * @param       $tokenType
     * @param array $stack
     * @return bool
     * @throws LexerException
     */
    protected function checkOpenTokens( $name , $tokenType , array &$stack ) {

        if ( $this->isOpenToken( $tokenType ) )
            $stack[] = $this->getCloseToken($tokenType);

        elseif ( $this->isCloseToken( $tokenType ) && !empty($stack) && end($stack) != $tokenType )
            $this->throwException( 'Invalid close token `%s`' , $name );

        elseif ( $this->isCloseToken( $tokenType ) && empty( $stack ) )
            return false;

        return true;
    }

    /**
     * @param array $stack
     */
    protected function checkStack( array $stack ) {
        if ( !empty( $stack ) ) {
            $this->throwException(
                'Close token not found for `%s`' ,
                $this->getTokenName( $this->getOpenToken( end($stack) ) )
            );
        }
    }

    /**
     * Tokenize expression
     *
     * @param null $exitToken
     * @return bool
     * @throws LexerException
     */
    protected function tokenizeExpression( $exitToken = null ) {
        $stack = [];
        return $this->tokenizeByTokens( $this->expressionBlockTokens , $stack , $exitToken );
    }

    /**
     * Strip spaces
     */
    protected function stripSpaces() {
        $this->reader->matchAndGo( '/\s+/A' );
    }

    /**
     * @param array $tokens
     * @param null  $stack
     * @param null  $exitToken
     * @return bool
     * @throws LexerException
     * @internal param bool $allowBlocks
     */
    protected function tokenizeByTokens( array $tokens , &$stack = null , $exitToken = null ) {

        // If any tokens found
        $any = false;

        // Stack for opened parenthesis,closures and brackets
        if ( !$stack ) $stack = [];

        $n = 0;

        do {

            // Check for exit
            if ( $exitToken && empty( $stack ) && $this->reader->strippedMatch( $exitToken ) !== false )
                break;

            // Strip white spaces
            $this->stripSpaces();

            // Found flag
            $found = false;

            foreach ( $tokens as $tokenType => $regexp ) {

                $mustComeAfter = null;

                if ( is_array( $regexp ) )
                    list( $regexp , $mustComeAfter ) = $regexp;

                if ( $mustComeAfter && !in_array( $this->tokens->last()->getType() , $mustComeAfter ) )
                    continue;

                if ( empty($regexp) )
                    $this->throwException( 'Regex for token "%s" is empty' , $this->getTokenName( $tokenType ) );

                if ( ( $name = $this->reader->matchAndGo( $regexp ) ) !== false ) {

                    // Empty token
                    if ( strlen( $name ) == 0 )
                        $this->throwException( 'Invalid language token `%s`' , $regexp );

                    // Open context token
                    if ( $this->isOpenToken( $tokenType ) )
                        $stack[] = $this->getCloseToken( $tokenType );
                    // Close context token
                    elseif ( end($stack) == $tokenType )
                        array_pop( $stack );
                    // Unexpected close token
                    elseif ( $this->isCloseToken( $tokenType ) && end($stack) != $tokenType )
                        $this->throwException('Unexpected close token `%s`, `%s` expected', $name, end($stack));

                    // Add token to tokens collections
                    $this->addToken( $name, $tokenType );

                    // Set found flag
                    $any = $found = true;

                    break;

                }

            }

            # Check infinite loop
            if ( $n++ > 50 )
                $this->throwException('Infinite loop %d',$found);

        } while ( !$this->reader->isEnd() && $found );

        # Stack
        $this->checkStack( $stack );

        return $any;

    }

    /**
     * Closure block tokenize
     * @return bool
     * @deprecated
     */
    protected function tokenizeClosureBlock() {
        $stack = [];
        return $this->tokenizeByTokens(
            $this->expressionBlockTokens,
            $stack,
            $this->getBlockEndToken( BlockType::BLOCK_CLOSURE )
        );
    }

    /**
     * Find close character position
     *
     * @param string    $text             Text
     * @param string    $openChar         Open character
     * @param string    $closeChar        Close character
     * @param int       $offset           Search start offset
     * @return bool|int                   Close token offset
     */
    protected function findClosePosition( $text , $openChar , $closeChar , $offset = 0 ) {

        $openPos = $closePos = $found = false;
        $text = substr( $text , $offset );

        while (true) {

            $closePos = strpos($text, $closeChar , $closePos !== false ? $closePos + 1 : 0 );
            $openPos = strpos($text, $openChar , $openPos !== false ? $openPos + 1 : 0 );

            if ( $closePos === false ) {
                return false;
            }
            else if ( $openPos === false || $openPos > $closePos )
                return $offset + $closePos;

        };

    }

    /**
     * @param $tokenType
     * @return mixed
     */
    protected function getRegex( $tokenType ) {
        return $this->regex[ $tokenType ];
    }

    /**
     * Tokenize section
     *
     * @return bool
     * @throws LexerException
     */
    protected function tokenizeSectionBlock() {

        $stack = [];

        // Parse section name ( Section type )
        if ( ( $sectionType = $this->reader->matchAndGo( $this->getRegex( TokenTypes::T_SECTION_TYPE ) ) ) === false )
            $this->throwException( 'Invalid section type' );

        // Add section type to tokens
        $this->addToken( $sectionType , TokenTypes::T_SECTION_TYPE );

        // Tokenize expression
        $this->tokenizeExpression(
            $this->getBlockEndToken( BlockType::BLOCK_SECTION )
        );

        // Find close token `}` if section start by brace
        if ( $this->reader->match( $this->getRegex( TokenTypes::T_OPEN_BRACE ) ) ) {
            $closePosition = $this->findClosePosition(
                $this->reader->readAll() ,
                '{' , '}' ,
                $this->reader->getCursor() + 1 );

            // Throw Exception if close token not found
            if ( $closePosition === false )
                $this->throwException('Section close brace not found');

            $this->addBreakpoint( $closePosition , '/\}/A' , TokenTypes::T_SECTION_CLOSE );

        }

    }

    protected function tokenizeCommentBlock() {

        $endPos = $this->findClosePosition( $this->reader->readAll() , '/*' , '*/' , $this->reader->getCursor() );

        $length = $endPos - $this->reader->getCursor();

        $comment = $this->reader->readAndGo( $length );

        $this->addToken( $comment , TokenTypes::T_COMMENT );

    }

    /**
     * Tokenize print block
     */
    protected function tokenizePrintBlock() {

        if ( $openParan = $this->reader->matchAndGo( $this->regex[ TokenTypes::T_OPEN_PARAN ] ) ) {
            $closeParanRegex = $this->regex[ $this->getCloseToken( TokenTypes::T_OPEN_PARAN ) ];
            $stack = [];
            $this->tokenizeExpression( $closeParanRegex );
            if ( !$this->reader->strippedMatchAndGo( $closeParanRegex ) )
                $this->throwException( 'Close parenthesis not found' );
        } else {

            while (!$this->reader->isEnd()) {
                foreach ($this->printBlockTokens as $tokenType => $regex) {
                    if ($name = $this->reader->matchAndGo($regex)) {

                        $this->addToken(
                            $name,
                            $tokenType
                        );

                        # Expression ( Call )
                        if ($paranToken = $this->reader->matchAndGo($this->regex[ TokenTypes::T_OPEN_PARAN ])) {

                            $this->addToken(
                                $paranToken,
                                TokenTypes::T_OPEN_PARAN
                            );

                            $this->tokenizeExpression(
                                $this->regex[ TokenTypes::T_CLOSE_PARAN ]
                            );

                            $this->stripSpaces();

                            if (!$paranClose = $this->reader->matchAndGo($this->getRegex(TokenTypes::T_CLOSE_PARAN))) {
                                $this->throwException('Close parenthesise not found');
                            }

                            $this->addToken(
                                $paranClose,
                                TokenTypes::T_CLOSE_PARAN
                            );

                        }

                        if ( $filter = $this->reader->match( $this->regex[ TokenTypes::T_FILTER ] ) ) {

                            $this->tokenizeExpression();

                        }

                        return true;
                    }
                }
            }

        }

        return false;
    }

    protected function tokenizeBlock() {
        $this->throwException('Code section feature not implemented yet');
    }

    /**
     * @param $tokenType
     * @return bool|int|string
     */
    public function getTokenName( $tokenType ) {
        return TokenTypes::getName( $tokenType );
    }

    /**
     * @param integer   $startOffset
     * @param string    $rawText
     * @throws LexerException
     */
    protected function addRawToken( $startOffset , $rawText ) {

        $break = false;
        $reader = new Reader( $rawText );
        $limitOffset = $startOffset + $reader->count();

        foreach ( $this->breakpoints as $i => $breakpoint ) {

            if ( $breakpoint->getOffset() <= $limitOffset ) {

                $break = true;

                # Breakpoint before
                $before = $reader->readAndGo(
                    $breakpoint->getOffset() - $startOffset # Breakpoint offset - rawText start offset ( Global position )
                );

                if ( $before != '' )
                    $this->addToken( $before , TokenTypes::T_RAW );

                # Breakpoint
                $breakToken = $reader->matchAndGo(
                    $breakpoint->getToken()
                );

                if ( $breakToken === false )
                    $this->throwException( 'Breakpoint token not matched !' );

                $this->addToken( $breakToken , $breakpoint->getTokenType() );

                $startOffset += $reader->getCursor();

                unset( $this->breakpoints[ $i ] );

            }
        }

        if ( !$break )
            $this->addToken(
                $rawText,
                TokenTypes::T_RAW
            );
        else
            if ( !$reader->isEnd() )
                $this->addToken(
                    $reader->readToEnd(),
                    TokenTypes::T_RAW
                );

    }

    /**
     * Tokenize
     *
     * @return Token[]|TokenCollection
     * @throws LexerException
     */
	public function tokenize() {

		# Estimate code blocks
		$this->codeBlockPositions = array();
		preg_match_all( $this->codeBlockRegex,  $this->reader->readToEnd() , $matched , PREG_OFFSET_CAPTURE );
		$matched = current( $matched );
		foreach ( $matched as $match ) $this->codeBlockPositions[] = $match[1];

        # No Code exists
        if ( empty( $this->codeBlockPositions ) )
            $this->addToken(
                $this->reader->readToEndAndGo(),
                TokenTypes::T_RAW
            );

		# Tokenize
		foreach ( $this->codeBlockPositions as $blockPosition ) {

            if ( $this->reader->getCursor() > $blockPosition ) continue;

            $blockType = null;

            # Add to raw block
            if ( $blockPosition > $this->reader->getCursor() ) {

                $this->addRawToken(
                    $this->reader->getCursor(),
                    $this->reader->read( $blockPosition - $this->reader->getCursor() )
                );

            }

			# Change cursor position
			$this->reader->setCursor( $blockPosition );

            # Add code block token
            if ( $_startToken = $this->reader->matchAndGo( $this->regex[ TokenTypes::T_CODE_BLOCK ] ) )
                $this->addToken( $_startToken , TokenTypes::T_CODE_BLOCK );
            else
                throw new LexerException('Code block token not found');

            # Block Type and block token
            foreach ( $this->codeBlocksRegex as $_blockType => $blockRegex ) {
                if ( ($_blockToken = $this->reader->matchAndGo($this->getBlockStartToken($_blockType))) !== false ) {
                    $this->addToken(
                        $_blockToken,
                        $this->codeBlocksTokens[ $_blockType ][0]
                    );
                    $blockType = $_blockType;
                    break;
                }
            }

            # Throw error if block type not determined
            if ( !$blockType )
                throw new LexerException( 'Invalid block type at line %d column %d' , $this->reader->getLine() , $this->reader->getColumn() );

            call_user_func( [ $this , $this->blockTokenizer[ $blockType ] ] );

            # Match block end token
            if ( ( $_blockEndToken = $this->reader->matchAndGo($this->getBlockEndToken( $blockType )) ) === false ) {
                throw new LexerException(
                    'Missing block end at line %d column %d, "%s"',
                    $this->reader->getLine(),
                    $this->reader->getColumn(),
                    str_replace("\n", " ", $this->reader->read(5))
                );
            }

            # Add block end token to tokens list
            $this->addToken(
                $_blockEndToken ,
                $this->codeBlocksTokens[ $blockType ][1]
            );

		}

        # No Code exists
        if ( !$this->reader->isEnd() && trim($this->reader->readToEnd(),"\n") != '' )
            $this->addRawToken(
                $this->reader->getCursor(),
                $this->reader->readToEnd()
            );

        # EOF
        $this->addToken( '' , TokenTypes::T_EOF );

		return $this->tokens;

	}

    /**
     * Add breakpoint for raw contents
     *
     * @param integer   $offset       Token offset
     * @param string    $token        Regular expression
     * @param integer   $tokenType    Token type
     */
    protected function addBreakpoint( $offset , $token , $tokenType ) {
        $this->breakpoints[] = new Breakpoint( $offset , $token , $tokenType );

        usort( $this->breakpoints , function( Breakpoint $a , Breakpoint $b ){
            return $a->getOffset() > $b->getOffset();
        });

    }

}
?>
