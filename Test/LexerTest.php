<?php
namespace Azera\Fry;

use Azera\Fry\Exception\Exception;
use Azera\Fry\Exception\LexerException;
use Azera\Fry\TokenTypes as T;

class LexerTest extends \PHPUnit_Framework_TestCase {

    public function __testLexer() {

        $text = '@Alireza';

        $lexer = new Lexer( new Reader( $text ) );
        $tokens = $lexer->tokenize();

        $this->assertCount( 2 , $tokens );

        $this->assertEquals( 0 , $tokens[0]->getIndex() );
        $this->assertEquals( '@' , $tokens[0]->getName() );
        $this->assertEquals( 'Alireza' , $tokens[0]->next()->getName() );

        $this->assertTrue( $tokens[0]->is( TokenTypes::T_CODE_BLOCK ) );

        $text = 'Welcome @Alireza';

        $lexer = new Lexer( new Reader( $text ) );
        $tokens = $lexer->tokenize();


    }

    public function testNewline() {

        $data = <<<DATE
Hello
    World
DATE;

        $data = str_replace( [ "\r\n" , "\n" ] , "\n" , $data );

        $this->assertGreaterThan( 0, preg_match( '/Hello$/Aim' , $data ) );


    }

    public function testFindClosePosition() {

        $mock = $this->getMockBuilder(Lexer::class)->disableOriginalConstructor()->getMock();

        $lexer = new \ReflectionClass(Lexer::class);
        $method = $lexer->getMethod('findClosePosition');
        $method->setAccessible(true);

        $this->assertEquals( 8 , $method->invoke( $mock , '{ { } } }' , '{' , '}' ) );

        $data = <<<DATA

                @if 3 > 4 {
                <div>3 is greater than 4 !?!</div>
                    @if {  }
                }

DATA;

        $this->assertEquals( 129 , $method->invoke( $mock , $data , '{' , '}' , 29 ) );


    }

    public function contexts() {
        return array(
            0 => [
                '@Masoud',
                [
                    [ T::T_CODE_BLOCK         , '@' ],
                    [ T::T_BLOCK_PRINT_OPEN   , '' ],
                    [ T::T_NAME               , 'Masoud' ],
                    [ T::T_BLOCK_PRINT_CLOSE  , '' ]
                ]
            ],
            1 => [
                '@Hello Reza',
                [
                    [ T::T_CODE_BLOCK         , '@' ],
                    [ T::T_BLOCK_PRINT_OPEN   , '' ],
                    [ T::T_NAME               , 'Hello' ],
                    [ T::T_BLOCK_PRINT_CLOSE  , '' ],
                    [ T::T_RAW                , ' Reza' ]
                ]
            ],
            2 => [
                'Welcome @Masoud',
                [
                    [ T::T_RAW                , 'Welcome ' ],
                    [ T::T_CODE_BLOCK         , '@' ],
                    [ T::T_BLOCK_PRINT_OPEN   , '' ],
                    [ T::T_NAME               , 'Masoud' ],
                    [ T::T_BLOCK_PRINT_CLOSE  , '' ],
                ]
            ],
            3 => [
                'mdzzohrabi@gmail.com', [
                    [ TokenTypes::T_RAW       , 'mdzzohrabi@gmail.com' ]
                ]
            ],
            4 => [
                '<title>@Title</title>',
                [
                    [ T::T_RAW                , '<title>' ],
                    [ T::T_CODE_BLOCK         , '@' ],
                    [ T::T_BLOCK_PRINT_OPEN   , '' ],
                    [ T::T_NAME               , 'Title' ],
                    [ T::T_BLOCK_PRINT_CLOSE  , '' ],
                    [ T::T_RAW                , '</title>' ]
                ]
            ],
            5 => [
                '<div class="@ClassName">@Name</div>',
                [
                    [ T::T_RAW                , '<div class="' ],
                    [ T::T_CODE_BLOCK         , '@' ],
                    [ T::T_BLOCK_PRINT_OPEN   , '' ],
                    [ T::T_NAME               , 'ClassName' ],
                    [ T::T_BLOCK_PRINT_CLOSE  , '' ],
                    [ T::T_RAW                , '">' ],
                    [ T::T_CODE_BLOCK         , '@' ],
                    [ T::T_BLOCK_PRINT_OPEN   , '' ],
                    [ T::T_NAME               , 'Name' ],
                    [ T::T_BLOCK_PRINT_CLOSE  , '' ],
                    [ T::T_RAW                , '</div>' ]
                ]
            ],
            6 => [
                '@( Name )',[
                    [ T::T_CODE_BLOCK          , '@' ],
                    [ T::T_BLOCK_PRINT_OPEN    , '' ],
                    [ T::T_NAME                , 'Name' ],
                    [ T::T_BLOCK_PRINT_CLOSE   , '' ]
                ]
            ],
            7 => [
            '@( First + Last )',[
                    [ T::T_CODE_BLOCK , '@' ],
                    [ T::T_BLOCK_PRINT_OPEN, '' ],
                    [ T::T_NAME , 'First' ],
                    [ T::T_OPERATOR , '+' ],
                    [ T::T_NAME , 'Last' ],
                    [ T::T_BLOCK_PRINT_CLOSE , '' ]
                ]
            ],
            8 => [
                '@( ) Hello World',[
                    [ T::T_CODE_BLOCK , '@' ],
                    [ T::T_BLOCK_PRINT_OPEN , '' ],
                    [ T::T_BLOCK_PRINT_CLOSE , '' ],
                    [ T::T_RAW , ' Hello World' ]
                ]
            ],
            9 => [
                '@Masoud()', [
                    [ T::T_CODE_BLOCK, '@' ],
                    [ T::T_BLOCK_PRINT_OPEN, '' ],
                    [ T::T_NAME, 'Masoud' ],
                    [ T::T_OPEN_PARAN, '(' ],
                    [ T::T_CLOSE_PARAN, ')' ],
                    [ T::T_BLOCK_PRINT_CLOSE , '' ]
                ]
            ],
            10 => [
                '@Say("Hello") World',[
                    [ T::T_CODE_BLOCK, '@' ],
                    [ T::T_BLOCK_PRINT_OPEN , ''],
                    [ T::T_NAME, 'Say' ],
                    [ T::T_OPEN_PARAN, '(' ],
                    [ T::T_STR, '"Hello"' ],
                    [ T::T_CLOSE_PARAN, ')' ],
                    [ T::T_BLOCK_PRINT_CLOSE, '' ],
                    [ T::T_RAW , ' World' ]
                ]
            ],
            11 => [
                '<p>@( "Hello" + "World" )</p>',[
                    [ T::T_RAW, '<p>' ],
                    [ T::T_CODE_BLOCK, '@' ],
                    [ T::T_BLOCK_PRINT_OPEN, '' ],
                    [ T::T_STR, '"Hello"' ],
                    [ T::T_OPERATOR, '+' ],
                    [ T::T_STR, '"World"' ],
                    [ T::T_BLOCK_PRINT_CLOSE, ''],
                    [ T::T_RAW , '</p>' ]
                ]
            ],
            12 => [
                '
                @if 3 > 4
                <div>3 is greater than 4 !?!</div>
                @endif
                ',
                [
                    [ T::T_RAW, "\n                " ],
                    [ T::T_CODE_BLOCK, '@' ],
                    [ T::T_SECTION_START, '' ],
                    [ T::T_SECTION_TYPE, 'if' ],
                    [ T::T_NUM, '3' ],
                    [ T::T_OPERATOR, '>' ],
                    [ T::T_NUM, '4' ],
                    [ T::T_SECTION_OPEN, "\n" ],
                    [ T::T_RAW, "                <div>3 is greater than 4 !?!</div>\n                " ],
                    [ T::T_CODE_BLOCK, '@' ],
                    [ T::T_SECTION_START, '' ],
                    [ T::T_SECTION_TYPE, 'endif' ],
                    [ T::T_SECTION_OPEN, "\n" ],
                    [ T::T_RAW , '                ' ]
                ]
            ],
            13 => [
                '
                @if 3 > 4 {
                <div>3 is greater than 4 !?!</div>
                }
                ',
                [
                    [ T::T_RAW, "\n                " ],
                    [ T::T_CODE_BLOCK, '@' ],
                    [ T::T_SECTION_START, '' ],
                    [ T::T_SECTION_TYPE, 'if' ],
                    [ T::T_NUM, '3' ],
                    [ T::T_OPERATOR, '>' ],
                    [ T::T_NUM, '4' ],
                    [ T::T_SECTION_OPEN, "{" ],
                    [ T::T_RAW, "\n                <div>3 is greater than 4 !?!</div>\n                " ],
                    [ T::T_SECTION_CLOSE, '}' ],
                    [ T::T_RAW , "\n                " ]
                ]
            ],
            14 => [
                '
                @if 3 > 4 {
                <div>3 is greater than 4 !?!</div>
                    @if {  }
                }
                ',
                [
                    [ T::T_RAW, "\n                " ],
                    [ T::T_CODE_BLOCK, '@' ],
                    [ T::T_SECTION_START, '' ],
                    [ T::T_SECTION_TYPE, 'if' ],
                    [ T::T_NUM, '3' ],
                    [ T::T_OPERATOR, '>' ],
                    [ T::T_NUM, '4' ],
                    [ T::T_SECTION_OPEN, '{' ],
                    [ T::T_RAW, "\n                <div>3 is greater than 4 !?!</div>\n                    " ],
                    [ T::T_CODE_BLOCK, '@' ],
                    [ T::T_SECTION_START, '' ],
                    [ T::T_SECTION_TYPE, 'if' ],
                    [ T::T_SECTION_OPEN, '{' ],
                    [ T::T_RAW , '  ' ],
                    [ T::T_SECTION_CLOSE, '}' ],
                    [ T::T_RAW, "\n                " ],
                    [ T::T_SECTION_CLOSE, '}' ],
                    [ T::T_RAW , "\n                " ]
                ]
            ],
            15 => [
                '@("Hello World" + 12)',
                [
                    [ T::T_CODE_BLOCK , '@' ],
                    [ T::T_BLOCK_PRINT_OPEN , '' ],
                    [ T::T_STR , '"Hello World"' ],
                    [ T::T_OPERATOR , '+' ],
                    [ T::T_NUM , '12' ],
                    [ T::T_BLOCK_PRINT_CLOSE , '' ]
                ]
            ],
            16 => [
                '@("Hello World" + 12 + ( 10 - 9 ))',
                [
                    [ T::T_CODE_BLOCK , '@' ],
                    [ T::T_BLOCK_PRINT_OPEN , '' ],
                    [ T::T_STR , '"Hello World"' ],
                    [ T::T_OPERATOR , '+' ],
                    [ T::T_NUM , '12' ],
                    [ T::T_OPERATOR , '+' ],
                    [ T::T_OPEN_PARAN , '(' ],
                    [ T::T_NUM , '10' ],
                    [ T::T_OPERATOR , '-' ],
                    [ T::T_NUM , '9' ],
                    [ T::T_CLOSE_PARAN , ')' ],
                    [ T::T_BLOCK_PRINT_CLOSE , '' ]
                ]
            ],
            17 => [
                '@if user is defined
                Hello @user.name
                @endif',
                [
                    [ T::T_CODE_BLOCK , '@' ],
                    [ T::T_SECTION_START , '' ],
                    [ T::T_SECTION_TYPE , 'if' ],
                    [ T::T_NAME , 'user' ],
                    [ T::T_OPERATOR , 'is' ],
                    [ T::T_NAME , 'defined' ],
                    [ T::T_SECTION_OPEN , "\n" ],
                    [ T::T_RAW , '                Hello ' ],
                    [ T::T_CODE_BLOCK , '@' ],
                    [ T::T_BLOCK_PRINT_OPEN , "" ],
                    [ T::T_NAME , 'user.name' ],
                    [ T::T_BLOCK_PRINT_CLOSE , '' ],
                    [ T::T_RAW , "\n                " ],
                    [ T::T_CODE_BLOCK , '@' ],
                    [ T::T_SECTION_START , '' ],
                    [ T::T_SECTION_TYPE , 'endif' ],
                    [ T::T_SECTION_OPEN , '' ],
                ]
            ],

//            17 => [
//                '@{
//
//                }',
//                [
//                    [ T::T_CODE_BLOCK , '@' ],
//                    [ T::T_BLOCK_OPEN , '{' ],
//                    [ T::T_BLOCK_CLOSE , '}' ]
//                ]
//            ]
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Environment
     */
    public function getEnvironment() {
        return $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @dataProvider    contexts
     * @param $context
     * @param $tokensType
     */
    public function testContext( $context , $tokensType ) {

        $lexer = new Lexer( new Reader( $context ) , $this->getEnvironment() );

        $tokens = $lexer->tokenize();

        $expected = [];
        $actual = [];

        foreach ( $tokensType as $item )
            $expected[] = $lexer->getTokenName( $item[0] ) . " (" . $item[1] . ")";

        foreach ( $tokens->getTokens() as $token ) {
            if ( $token->getType() == TokenTypes::T_EOF ) continue;
            $actual[] = $lexer->getTokenName( $token->getType() )  . " (" . $token->getName() . ")";
        }

        $this->assertEquals( $expected , $actual );
//
//        $tokensCount = count( $tokensType );
//        if ( $tokensCount != $tokens->count() ) {
//            $this->assertEquals( $tokensType , $tokens->getTokens());
//        }
//
//        foreach ( $tokensType as $i => $token ) {
//            $this->assertEquals(
//                $token[0],
//                $tokens[$i]->getType(),
//                sprintf(
//                    'Invalid token `%s` at token offset %d, it must be `%s`' ,
//                    $lexer->getTokenName( $tokens[$i]->getType() ) ,
//                    $i,
//                    $lexer->getTokenName( $token[0] )
//                )
//            );
//            $this->assertEquals(
//                $token[1],
//                $tokens[$i]->getName(),
//                sprintf(
//                    'Invalid token name `%s` at token offset %d, it must be `%s`',
//                    $tokens[$i]->getName(),
//                    $i,
//                    $token[1]
//                )
//            );
//        }

    }

    public function invalidContext() {
        return array(
            ['@Hello('],
            ['@('],
            ['@)'],
            ['@( ( )']
        );
    }

    /**
     * @dataProvider invalidContext
     * @expectedException \Azera\Fry\Exception\LexerException
     * @param $context
     */
    public function testInvalidContext( $context ) {

        (new Lexer( new Reader( $context ) , $this->getEnvironment() ))->tokenize();

    }


}
?>
