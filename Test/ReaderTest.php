<?php
namespace Azera\Fry;

class ReaderTest extends \PHPUnit_Framework_TestCase {

	public function testReader() {

		$text = <<<HTML
HELLO WORLD
HTML;

		$reader = new Reader( $text );

		$this->assertEquals( strlen( $text ) , strlen(trim($text)) );
		$this->assertEquals( "HE" , $reader->read(2) );
		$this->assertEquals( strlen( $text ) , $reader->length() );
		$this->assertEquals( 6 , $reader->moveCursor(6)->getCursor() );
		$this->assertEquals( 'WORLD' , $reader->readToEnd() );
		$this->assertEquals( 'HELLO WORLD' , $reader->rewind()->readToEnd() );
		$this->assertEquals( 'HELLO ' , $reader->readAndGo(6) );
		$this->assertEquals( 'WORLD' , $reader->readToEnd() );

		# Reset position
		$reader->rewind();

        $this->assertNotFalse( $reader->match('/(?=[a-z])/iA') );
		$this->assertEquals( 'HELLO' , $reader->match('/\w+/A') );
		$this->assertEquals( 0 , $reader->getCursor() );

		$this->assertFalse( $reader->matchAndGo('/\d+/A') );
		$this->assertEquals( 'HELLO' , $reader->matchAndGo('/\w+/A') );
		$this->assertEquals( ' ' , $reader->matchAndGo('/\s+/A') );
		$this->assertEquals( 'WORLD' , $reader->matchAndGo('/\w+/A') );

		$this->assertTrue( $reader->isEnd() );

		$reader->rewind();

		$this->assertEquals( 'HELLO WORLD' , $reader->readToEndAndGo() );
		$this->assertEquals( 11 , $reader->length() );
		$this->assertEquals( 10 , $reader->getCursor() );
		$this->assertTrue( $reader->isEnd() );

		$reader = new Reader('
		Hello
		World
		');

		$reader->setCursor( strpos($reader->readAll(),'World') );

		$this->assertEquals( 3 , $reader->getLine() );
		$this->assertEquals( 2 , $reader->getColumn() );

	}

}
?>
