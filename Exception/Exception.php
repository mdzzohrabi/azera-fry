<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Exception;

use Exception as BaseException;

class Exception extends BaseException
{

    public function __construct( $message , ...$params ) {
        parent::__construct( sprintf( $message , ...$params ) , 500 );
    }

}