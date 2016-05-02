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

class Macro extends Node
{

    const NODE_ARGS = 'args';
    const NODE_BODY = 'body';
    const ATTR_NAME = 'name';

    /**
     * Macro constructor.
     *
     * @param string                  $name
     * @param Node                    $args
     * @param Node                    $body
     * @param int                     $lineNo
     */
    public function __construct( $name , Node $args , Node $body , $lineNo)
    {
        parent::__construct([
            self::NODE_BODY => $body,
            self::NODE_ARGS => $args
        ], [
            self::ATTR_NAME => $name
        ], $lineNo);
    }

    public function compile(Compiler $compiler)
    {

        $args = $this->getNode( self::NODE_ARGS )->getNodes();
        $macroName = $this->getAttribute(self::ATTR_NAME);

        $compiler
            ->writeln('// Line ' . $this->getLineNo())
            ->write('public function macro_' . $macroName )
            ->scopeIn( Compiler::SCOPE_MACRO , $macroName )
            ->raw('( $context = [] , $arguments = [] ) {')
            ->line()
            ->indent()
            ->writeln('$args = [')
            ->indent()
        ;

        foreach ( $args as $name => $value ) {
            $compiler
                ->write('')
                ->string($name)
                ->raw(' => ');

            if ($value) {
                $compiler->subcompile($value);
            } else {
                $compiler->raw('null');
            }

            $compiler
                ->raw(',')
                ->line();

        }

        $compiler
            ->outdent()
            ->writeln('];')
            ->writeln('$context = array_merge( $context , $this->prepareArgs( $args , $arguments ) );')
            ->subcompile( $this->getNode( self::NODE_BODY ) )
            ->scopeOut()
            ->outdent()
            ->writeln('}')
        ;

    }

}