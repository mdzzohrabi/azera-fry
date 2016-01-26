<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Test\Parser;

use Azera\Fry\Extension\Core;
use Azera\Fry\Node;
use Azera\Fry\Node\Expression\Constant;
use Azera\Fry\Node\Expression\Filter;
use Azera\Fry\Node\Expression\Operand;
use Azera\Fry\Parser;
use Azera\Fry\Test\TestCase;
use Azera\Fry\Token;
use Azera\Fry\TokenCollection;
use Azera\Fry\TokenStream;
use Azera\Fry\TokenTypes;

/**
 * Class ExpressionParser
 *
 * @package Azera\Fry\Test\Parser
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class ExpressionParserTest extends TestCase
{

    public function expressionParser() {
        return array(
            0 => [
                [
                    new Token( null , '"Masoud Zohrabi"' , TokenTypes::T_STR , 0 , 0 )
                ],
                new Constant( '"Masoud Zohrabi"' , 0 )
            ],
            1 => [
                [
                    new Token( null , '"Masoud"' , TokenTypes::T_STR , 0 , 0 ),
                    new Token( null , '+' , TokenTypes::T_OPERATOR , 0 , 0 ),
                    new Token( null , '"Zohrabi"' , TokenTypes::T_STR , 0 , 0 )
                ],
                new Node\Expression\Binary(
                    new Constant( '"Masoud"' , 0 ),
                    new Constant( '"Zohrabi"' , 0 )
                , '+' ,0)
            ],
            2 => [
                [
                    new Token( null , '"Masoud"' , TokenTypes::T_STR , 0 , 0 ),
                    new Token( null , '+' , TokenTypes::T_OPERATOR , 0 , 0 ),
                    new Token( null , '"Zohrabi"' , TokenTypes::T_STR , 0 , 0 ),
                    new Token( null , '|' , TokenTypes::T_FILTER , 0 , 0 ),
                    new Token( null , 'lower' , TokenTypes::T_NAME , 0 , 0 )
                ],
                new Node\Expression\Binary(
                    new Constant( '"Masoud"' , 0 ),
                    new Filter(
                        new Constant( '"Zohrabi"' , 0 ),
                        'lower' ,
                        new Node\Expression\Arguments(),
                        0
                    )
                    , '+' , 0
                )
            ],
            3 => [
                [
                    new Token( null , '"Masoud"' , TokenTypes::T_STR , 0 , 0 ),
                    new Token( null , '|' , TokenTypes::T_FILTER , 0 , 0 ),
                    new Token( null , 'lower' , TokenTypes::T_NAME , 0 , 0 ),
                    new Token( null , '+' , TokenTypes::T_OPERATOR , 0 , 0 ),
                    new Token( null , '"Zohrabi"' , TokenTypes::T_STR , 0 , 0 ),
                    new Token( null , '|' , TokenTypes::T_FILTER , 0 , 0 ),
                    new Token( null , 'lower' , TokenTypes::T_NAME , 0 , 0 )
                ],
                new Node\Expression\Binary(
                    new Filter( new Constant( '"Masoud"' , 0 ), 'lower' , new Node\Expression\Arguments() , 0 ),
                    new Filter( new Constant( '"Zohrabi"' , 0 ), 'lower' , new Node\Expression\Arguments() , 0 ),
                    '+' , 0
                )
            ],
            4   => [
                [
                    new Token( null , 'render' , TokenTypes::T_NAME , 0,0 ),
                    new Token( null , '(' , TokenTypes::T_OPEN_PARAN , 0,0 ),
                    new Token( null , '"base.html"' , TokenTypes::T_STR , 0,0 ),
                    new Token( null , ')' , TokenTypes::T_CLOSE_PARAN , 0,0 ),
                ],
                new Node\Expression\Call(
                    'render',
                    new Node\Expression\Arguments([
                        new Constant( '"base.html"' , 0 )
                    ]),
                    0
                )
            ],
            5   => [
                [
                    new Token( null , 'user.name' , TokenTypes::T_NAME , 0,0 )
                ],
                new Node\Expression\Name( 'user.name' , 0 )
            ]
        );
    }

    /**
     * @dataProvider expressionParser
     */
    public function testExpressionParser( $tokens , $nodes ) {

        # Append EOF Token
        $tokens[] = new Token( null , '' , TokenTypes::T_EOF , 0 ,0 );

        $parser = new Parser( new TokenStream( $tokens ) , $this->getEnvironment()->addExtension( new Core() ) );
        $expr = new Parser\ExpressionParser( $parser );
        $node = $expr->parseExpression();

        $this->assertEquals( $nodes , $node );

    }

}