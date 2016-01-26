<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Loader;


use Azera\Fry\Exception\Exception;

class FileLoader implements LoaderInterface
{

    protected $directory;

    public function __construct( $directory )
    {
        $this->directory = $directory;
    }

    public function getSource($name)
    {
        $realName = $this->prepareName( $name );
        if ( !$this->exists( $realName ) )
            throw new Exception(sprintf( 'Template "%s" not found in %s' , $name , $this->directory ));

        return file_get_contents( $this->directory . '/' . $realName );

    }

    public function getCacheKey($name)
    {
        return sha1( $name );
    }

    public function isFresh($name, $time)
    {
        return false;
    }

    protected function exists( $realName ) {
        return file_exists( $this->directory . '/' . $realName );
    }

    protected function prepareName( $name ) {
        return preg_replace('/\:/','/',$name);
    }
}