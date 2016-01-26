<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry;


use Azera\Fry\Core\Filter;
use Azera\Fry\Core\Operator;
use Azera\Fry\Core\SimpleFunction;

abstract class AbstractExtension
{

    /**
     * @return SimpleFunction[]
     */
    public function getFunctions() {
        return array();
    }

    /**
     * @return Filter[]
     */
    public function getFilters() {
        return array();
    }

    /**
     * @return Operator[]
     */
    public function getBinaryOperators() {
        return array();
    }

    /**
     * @return Operator[]
     */
    public function getUnaryOperators() {
        return array();
    }

    public function getSections() {
        return array();
    }

}