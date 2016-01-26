<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Test;


use Azera\Fry\Environment;

class TestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Environment
     */
    public function getEnvironment() {
        return $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->setMethods(null)->getMock();
    }

}