<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry;

use Azera\Fry\Exception\SyntaxException;

/**
 * Class TokenStream
 *
 * @package Azera\Fry
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class TokenStream extends Stream
{

    protected $fileName;

    /**
     * @var Token[]
     */
    protected $items;

    /**
     * TokenStream constructor.
     *
     * @param Token[] $items
     * @param         $fileName
     */
    public function __construct(array $items , $fileName = null)
    {
        parent::__construct($items);
        $this->fileName = $fileName;
    }

    /**
     * @return bool
     */
    public function isEOF()
    {
        return $this->items[ $this->offset ]->getType() == TokenTypes::T_EOF;
    }

    /**
     * @return Token
     */
    public function next()
    {
        return parent::next();
    }

    /**
     * @return Token
     */
    public function prev()
    {
        return parent::prev();
    }

    /**
     * @return Token
     */
    public function getCurrent()
    {
        return parent::getCurrent();
    }

    /**
     * @return Token
     */
    public function getToken() {
        return parent::getCurrent();
    }

    /**
     * @param      $tokenTypes
     * @param null $values
     * @return bool
     */
    public function test( $tokenTypes , $values = null ) {
        return $this->getCurrent()->test( $tokenTypes , $values );
    }

    public function expect( $type , $value = null , $message = null ) {

        $token = $this->getCurrent();

        if ( !$token->test( $type , $value ) ) {

            throw new SyntaxException(
                '%sUnexpected token "%s" of value "%s" ("%s" expected%s) , on "%s" at line %d column %d' ,
                $message ,
                TokenTypes::getName( $token->getType() ),
                $token->getName(),
                TokenTypes::getName( $type ),
                ( $value ? sprintf( ' with value "%s"' , $value ) : '' ),
                $this->fileName,
                $token->getLine(),
                $token->getColumn()
            );

        }

        $this->next();

        return $token;

    }

    /**
     * @param int $number
     * @return Token
     * @throws \Exception
     */
    public function look( $number = 1 ) {
        return $this->getOffset( $this->offset + $number );
    }

    /**
     * @param      $tokenTypes
     * @param null $values
     * @return Token|bool
     */
    public function nextIf( $tokenTypes , $values = null ) {

        if ( $this->isEOF() ) return false;

        if ( $this->items[ $this->offset + 1 ]->test( $tokenTypes , $values ) )
            return $this->items[ ++$this->offset ];

        return false;
    }

    public function skipIf( $tokenTypes , $values = null ) {

        if ( $this->isEOF() ) return false;

        if ( $this->items[ $this->offset ]->test( $tokenTypes , $values ) )
            return $this;

        return false;

    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

}