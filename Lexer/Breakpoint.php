<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Lexer;

/**
 * Class Breakpoint
 *
 * @package Azera\Fry\Lexer
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class Breakpoint
{

    /**
     * @var string
     */
    protected $token;

    /**
     * @var integer
     */
    protected $tokenType;

    /**
     * @var integer
     */
    protected $offset;

    /**
     * @param integer   $offset
     * @param string    $token
     * @param integer   $tokenType
     */
    public function __construct( $offset , $token , $tokenType ) {

        $this->offset = $offset;
        $this->token = $token;
        $this->tokenType = $tokenType;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * @return mixed
     */
    public function getOffset()
    {
        return $this->offset;
    }

}