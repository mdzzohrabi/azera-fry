<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Test;

use Azera\Fry\Template;
use LogicException;

class TemplateTest extends TestCase
{

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Template
     */
    public function getTemplateMock() {
        $mock = $this
            ->getMockBuilder(Template::class)
            ->setConstructorArgs([ $this->getEnvironment() ])
            ->getMockForAbstractClass();

        return $mock;
    }

    public function testTemplate() {

        $template = $this->getTemplateMock();

        $context = [
            'name'  => 'Masoud',
            'user'  => [
                'name'  => 'Alireza'
            ]
        ];

        $this->assertEquals( 'Masoud' , $template->getValue( $context , ['name'] ) );
        $this->assertEquals( 'Alireza' , $template->getValue( $context , [ 'user' , 'name' ] ) );

        $this->setExpectedException( LogicException::class );
        $template->getValue( $context , [ 'user.name.id' ] );

    }

}