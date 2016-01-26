<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Test;


use Azera\Fry\Token;
use Azera\Fry\TokenStream;
use Azera\Fry\TokenTypes;

class TokenStreamTest extends \PHPUnit_Framework_TestCase
{

    public function testTokenStream() {

        $stream = new TokenStream([
            new Token( null , 'Hello' , TokenTypes::T_NAME ,0,0 ),
            new Token( null , '"World"' , TokenTypes::T_STR ,0,0 ),
            new Token( null , '' , TokenTypes::T_EOF ,0,0 ),
        ]);

        $this->assertTrue( $stream->test( TokenTypes::T_NAME , 'Hello' ) );
        $this->assertEquals( new Token( null , '"World"' , TokenTypes::T_STR , 0, 0 ) , $stream->nextIf( TokenTypes::T_STR ) );
        $this->assertEquals( new Token( null , '"World"' , TokenTypes::T_STR , 0, 0 ) , $stream->getCurrent() );
        $this->assertEquals( new Token( null , '"World"' , TokenTypes::T_STR , 0, 0 ) , $stream->expect(TokenTypes::T_STR));
        $this->assertTrue( $stream->isEOF() );

    }

}