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

class IfSection extends Node
{

    const NODE_ELSE = 'else';
    const NODE_TESTS = 'tests';

    /**
     * IfSection constructor.
     *
     * @param Node[]        $tests
     * @param Node|null     $else
     * @param int           $lineNo
     */
    public function __construct( array $tests , $else = null , $lineNo)
    {
        parent::__construct([
            self::NODE_TESTS    => $tests,
            self::NODE_ELSE     => $else
        ], [], $lineNo);
    }

    public function compile(Compiler $compiler)
    {

        /** @var Node[] $tests */
        $tests = $this->getNode(self::NODE_TESTS);

        for ( $i = 0 ; $i < count( $tests ) ; $i += 2 ) {

            $test = $tests[$i];
            $true = $tests[$i+1];

            if ( $i == 0 )
                $compiler->write('if ( ');
            else
                $compiler->raw( ' elseif ( ' );

            $compiler
                ->subcompile( $test )
                ->raw( ' ) { ' . "\n" )
                ->indent()
                ->subcompile( $true )
                ->outdent()
                ->write('}');

        }

        if ( $this->hasNode( self::NODE_ELSE ) ) {
            $compiler
                ->raw(' else { ' . "\n")
                ->indent()
                ->subcompile( $this->getNode( self::NODE_ELSE ) )
                ->outdent()
                ->write('}');
        }

        $compiler->line();

    }

}