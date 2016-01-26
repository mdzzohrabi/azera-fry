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

class Template extends Node
{

    const NODE_BODY = 'body';
    const NODE_BLOCKS = 'blocks';
    const NODE_FUNCTIONS = 'functions';
    const NODE_MACROS = 'macros';

    const ATTR_NAME = 'name';

    /**
     * Template constructor.
     *
     * @param array|\Azera\Fry\Node[] $name
     * @param Node                    $body
     * @param Node[]                  $blocks
     * @param Node[]                  $macros
     */
    public function __construct( $name , Node $body , $blocks , $macros )
    {
        parent::__construct([
            self::NODE_BODY         => $body,
            self::NODE_BLOCKS       => $blocks,
            self::NODE_MACROS       => $macros
        ], [
            self::ATTR_NAME     => $name ?: md5(rand(1000,9999))
        ], null);
    }

    public function getBody() {
        return $this->getNode(self::NODE_BODY);
    }

    public function compile(Compiler $compiler)
    {

        $name = $this->getAttribute( self::ATTR_NAME );

        $compiler
            ->write('<?php')
            ->line()
            ->write('class ' . $compiler->getEnvironment()->getTemplateClass( $name ) . ' extends Azera\Fry\Template {' . "\n" )
            ->line()
            ->indent()
        ;

        $this->renderTemplateName( $compiler );
        $this->renderBody( $compiler );
        $this->renderBlocks( $compiler );
        $this->renderMacros( $compiler );

        $compiler
            ->outdent()
            ->line()
            ->write('}')
        ;

    }

    protected function renderTemplateName( Compiler $compiler ) {

        $compiler
            ->writeln('public function getTemplateName() {')
            ->indent()
            ->write('return ')
            ->string( $this->getAttribute(self::ATTR_NAME) )
            ->raw(';')
            ->line()
            ->outdent()
            ->writeln('}')
            ->line();

    }

    protected function renderBlocks( Compiler $compiler ) {

        /** @var Node[] $blocks */
        $blocks = $this->getNode( self::NODE_BLOCKS );

        foreach ( $blocks as $block ) {
            $compiler->subcompile( $block );
            $compiler->line();
        }

    }

    protected function renderBody( Compiler $compiler ) {

        $compiler
            ->writeln('/**')
            ->writeln(' * Render template')
            ->writeln(' * @param array $context')
            ->writeln(' */')
            ->writeln('public function display( array $context = [] ) {')
            ->indent()
        ;

        $this->getBody()->compile( $compiler );

        $compiler
            ->outdent()
            ->writeln('}')
            ->line()
        ;

    }

    private function renderMacros( Compiler $compiler )
    {

        /** @var Node[] $macros */
        $macros = $this->getNode( self::NODE_MACROS );

        foreach ( $macros as $macro )
            $compiler->subcompile( $macro )->line();
    }

}