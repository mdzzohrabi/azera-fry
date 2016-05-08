<?php
/**
 * Created by PhpStorm.
 * User: Masoud
 * Date: 07/05/2016
 * Time: 06:55 PM
 */

namespace Azera\Fry\Loader;

/**
 * Class StreamLoader
 * @package Azera\Fry\Loader
 */
class StreamLoader implements LoaderInterface
{

    public function getSource($name)
    {
        return $name;
    }

    public function getCacheKey($name)
    {
        return sha1($name);
    }

    public function isFresh($name, $time)
    {
        return false;
    }
}