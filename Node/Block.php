<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Node;


use Azera\Fry\Compiler;
use Azera\Fry\Node;

class Block extends Node
{

    const NODE_BODY = 'body';
    const ATTR_NAME = 'name';

    /**
     * Block constructor.
     *
     * @param Node      $body
     * @param string    $name
     * @param int       $lineNo
     */
    public function __construct( $body , $name , $lineNo)
    {
        parent::__construct([
            self::NODE_BODY => $body
        ], [
            self::ATTR_NAME => $name
        ], $lineNo);
    }

    public function getName() {
        return $this->getAttribute(self::ATTR_NAME);
    }

    public function getMethodName() {
        return 'block_' . $this->getAttribute( self::ATTR_NAME );
    }

    public function compile(Compiler $compiler)
    {

        $blockName = $this->getAttribute( self::ATTR_NAME );


        $compiler
            ->writeln( '// Line ' . $this->getLineNo() )
            ->writeln( sprintf('public function %s( array $context = array() ) {' , $this->getMethodName() ) )
            ->indent()
            ->scopeIn( Compiler::SCOPE_BLOCK , $blockName )
            ->writeln('ob_start();')
            ->subcompile( $this->getNode( self::NODE_BODY ) )
            ->writeln('return ob_get_clean();')
            ->scopeOut()
            ->outdent()
            ->writeln('}')
        ;

    }

}