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

class LoopNode extends Node
{

    const ATTR_KEY = 'key';
    const ATTR_VALUE = 'value';
    const NODE_REPO = 'repo';
    const NODE_BODY = 'body';

    /**
     * LoopNode constructor.
     *
     * @param string                  $key
     * @param string                  $value
     * @param Node                    $repo
     * @param Node                    $body
     * @param                         $lineNo
     */
    public function __construct( $key , $value , Node $repo , Node $body , $lineNo)
    {
        parent::__construct([
            self::NODE_BODY => $body,
            self::NODE_REPO => $repo
        ], [
            self::ATTR_KEY => $key,
            self::ATTR_VALUE => $value
        ], $lineNo);
    }

    public function compile(Compiler $compiler)
    {

        $key = $this->hasAttribute( self::ATTR_KEY ) ? $this->getAttribute( self::ATTR_KEY ) : 'key';
        $value = $this->getAttribute( self::ATTR_VALUE );

        $compiler
            ->writeln('$context["_parent"] = $context;')
            ->writeln('$context["loop"] = [')->indent()
            ->write('"items" => $_seq = $this->ensureTraversable(')->subcompile( $this->getNode( self::NODE_REPO ) )->raw("),\n")
            ->writeln('"index" => 0,')
            ->writeln('"count" => count( $_seq ),')
//            ->writeln('"_parent" => $context')
            ->outdent()->writeln('];')
            ->write('foreach ( $context["loop"]["items"] as ')->raw( "\$$key" )->raw( ' => ' )->raw( "\$$value" )->raw( " ) { \n" )
            ->indent()
            ->writeln( sprintf( '$context["%s"] = $%s;' , $key , $key ) )
            ->writeln( sprintf( '$context["%s"] = $%s;' , $value , $value ) )
            ->subcompile( $this->getNode( self::NODE_BODY ) )
            ->writeln('$context["loop"]["index"]++;')
            ->outdent()
            ->writeln('}')
            ->writeln('$context = $context["_parent"];')
        ;

    }

}