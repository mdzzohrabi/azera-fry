<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry;

/**
 * Class Compiler
 *
 * @package Azera\Fry
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class Compiler
{

    /**
     * @var string
     */
    protected $source;

    /**
     * @var int
     */
    protected $indent = 0;
    /**
     * @var Environment
     */
    protected $environment;

    public function __construct( Environment $environment )
    {
        $this->environment = $environment;
    }

    public function compile( Node $node )
    {

        $this->source = '';
        $this->indent = 0;

        $node->compile( $this );

        return $this->source;

    }

    /**
     * @param int $step
     * @return $this
     */
    public function indent( $step = 1 ) {
        $this->indent += $step;
        return $this;
    }

    /**
     * @param int $step
     * @return $this
     */
    public function outdent( $step = 1 ) {
        $this->indent -= $step;
        return $this;
    }

    /**
     * @return $this
     */
    public function write() {
        foreach ( func_get_args() as $string )
            $this->source .= str_repeat(' ' , $this->indent * 4) . $string;
        return $this;
    }

    /**
     * @param Node $node
     * @return $this
     */
    public function subcompile( Node $node ) {
        $node->compile( $this );
        return $this;
    }

    /**
     * @param $string
     * @return Compiler
     */
    public function writeln( $string ) {
        return $this->write( $string . "\n" );
    }

    /**
     * @return $this
     */
    public function line() {
        $this->source .= "\n";
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function string($value)
    {
        $this->source .= sprintf('"%s"', addcslashes($value, "\0\t\"\$\\"));
        return $this;
    }

    /**
     * @param $text
     * @return $this
     */
    public function raw( $text ) {
        $this->source .= $text;
        return $this;
    }

    /**
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

}