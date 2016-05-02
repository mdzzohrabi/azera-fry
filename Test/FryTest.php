<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Test;


use Azera\Fry\Compiler;
use Azera\Fry\Extension\Core;
use Azera\Fry\Fry;
use Azera\Fry\Lexer;
use Azera\Fry\Loader\FileLoader;
use Azera\Fry\Template;
use Azera\Fry\Parser;
use Azera\Fry\Reader;

class FryTest extends TestCase
{

    public function dataCompile() {
        return array(
            [
                '@("Hello" ~ "World")',
                'echo "Hello" . "World";'
            ],
            [
                '@user.name',
                'echo $this->getValue( $context , ["user","name"] );'. "\n"
            ],
            [
                'Hello @user.name',
                "echo \"Hello \";\necho \$this->getValue( \$context , [\"user\",\"name\"] );\n"
            ]

        );
    }

    /**
     * @dataProvider dataCompile
     * @param $template
     * @param $compiled
     * @throws \Azera\Fry\Exception\Exception
     * @throws \Azera\Fry\Exception\LexerException
     */
    public function testCompiles( $template , $compiled ) {

        $env = $this->getEnvironment()->addExtension( new Core() );
        $lexer = new Lexer(new Reader( $template ) , $env );
        $parser = new Parser( $lexer->tokenize()->getStream() , $env );
        $bodyNode = $parser->parse()->getBody();
        $compiler = new Compiler( $this->getEnvironment() );
        $output = $compiler->compile( $bodyNode );
        $this->assertEquals( trim($compiled) , trim($output) );

    }

    private function getFry() {
        $TMP = __DIR__ . '/tmp';

        $fry = new Fry( new FileLoader( __DIR__ . '/Fixture' ) );
        $fry->setTempDir( $TMP );

        return $fry;
    }

    public function testFry_1() {

        $TMP = __DIR__ . '/tmp';
        $fry = $this->getFry();

        $template = $fry->loadTemplate( 'simple.html.fry' );

        $this->assertInstanceOf( Template::class , $template );

        $view = $template->render([
            'title' => 'Sample page',
            'user'  => [ 'name' => 'Masoud Zohrabi' ]
        ]);

        $this->assertRegExp('/Welcome Masoud/',$view);

        file_put_contents( $TMP . '/simple.fry.rendered.html' , $view );

    }

    public function testFry_2() {

        $fry = $this->getFry();

        $template = $fry->loadTemplate( 'layout.html.fry' );
        $title = $template->renderBlock( 'title' );
        $this->assertEquals( 'No Title' , trim( $title ) );

        $template = $fry->loadTemplate( 'page.html.fry' );

        $this->assertEquals( 'No Title Page 1' , trim( $template->renderBlock( 'title' ) ) );

    }

}
