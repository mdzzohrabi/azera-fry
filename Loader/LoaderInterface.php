<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Loader;


interface LoaderInterface
{

    public function getSource( $name );

    public function getCacheKey( $name );

    public function isFresh( $name , $time );

}