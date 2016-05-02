<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Node\Expression;


use Azera\Fry\Compiler;
use Azera\Fry\Node;

class Constant extends Node
{

    const TYPE_RAW = 0;
    const TYPE_STRING = 1;
    const TYPE_NUMBER = 2;
    const TYPE_KEYWORD = 3;

    const ATTR_TYPE = 'type';
    const ATTR_CONSTANT = 'constant';

    /**
     * Constant constructor.
     *
     * @param string $constant
     * @param int    $lineNo
     * @param int    $type
     */
    public function __construct( $constant , $lineNo, $type = self::TYPE_RAW )
    {
        parent::__construct([], [ self::ATTR_CONSTANT  => $constant , self::ATTR_TYPE => $type  ], $lineNo);
    }

    public function getValue() {
        return $this->getAttribute( self::ATTR_CONSTANT );
    }

    public function getType() {
        return $this->getAttribute( self::ATTR_TYPE );
    }

    public function compile(Compiler $compiler)
    {

        $compiler->raw( $this->getAttribute( 'constant' ) );

    }

}