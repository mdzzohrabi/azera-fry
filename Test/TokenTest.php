<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Test;


use Azera\Fry\Token;
use Azera\Fry\TokenCollection;
use Azera\Fry\TokenTypes;

class TokenTest extends \PHPUnit_Framework_TestCase
{

    public function testToken() {

        $token = new Token( null , '"Masoud Zohrabi"' , TokenTypes::T_STR , 0 , 0 );

        $this->assertTrue(
            $token->test( TokenTypes::T_STR )
        );

        $this->assertTrue(
            $token->test( TokenTypes::T_STR , '"Masoud Zohrabi"' )
        );

    }

}