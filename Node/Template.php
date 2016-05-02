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
    const NODE_PARENT = 'parent';

    const ATTR_NAME = 'name';

    /**
     * Template constructor.
     *
     * @param array|\Azera\Fry\Node[] $name
     * @param Node                    $parent
     * @param Node                    $body
     * @param Node[]                  $blocks
     * @param Node[]                  $macros
     */
    public function __construct( $name , Node $parent = null , Node $body , array $blocks = [] , array $macros = [] )
    {
        parent::__construct([
            self::NODE_BODY         => $body,
            self::NODE_BLOCKS       => $blocks,
            self::NODE_MACROS       => $macros,
            self::NODE_PARENT       => $parent
        ], [
            self::ATTR_NAME     => $name ?: md5(rand(1000,9999))
        ], null);
    }

    /**
     * @return Block[]
     */
    public function getBlocks() {
        return $this->getNode( self::NODE_BLOCKS );
    }

    public function getBody() {
        return $this->getNode(self::NODE_BODY);
    }

    public function compile(Compiler $compiler)
    {

        $compiler->scopeIn( Compiler::SCOPE_ROOT );

        $name = $this->getAttribute( self::ATTR_NAME );

        $compiler
            ->write('<?php')
            ->line()
            ->write('class ' . $compiler->getEnvironment()->getTemplateClass( $name ) . ' extends Azera\Fry\Template {' . "\n" )
            ->line()
            ->indent()
        ;

        $this->renderTemplateName( $compiler );
        $this->renderParentFunction( $compiler );
        $this->renderBody( $compiler );
        $this->renderBlocks( $compiler );
        $this->renderMacros( $compiler );

        $compiler
            ->outdent()
            ->line()
            ->write('}')
        ;

        $compiler->scopeOut();

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

        foreach ( $this->getBlocks() as $block ) {
            $block->compile( $compiler );
            $compiler->line();
        }

    }

    protected function renderBody( Compiler $compiler ) {

        $compiler
            ->writeln('public function __construct( $env ) {')
            ->indent()
            ->writeln('parent::__construct( $env );')
            ;

        if ( $this->hasParent() )
            $compiler
                ->writeln('// Line ' . $this->getNode(self::NODE_PARENT)->getLineNo() )
                ->writeln('$this->parent = $env->loadTemplate( $this->getParent() );');


        $compiler->writeln('$this->blocks = [')->indent();

        foreach ( $this->getBlocks() as $block ) {
            $compiler
                ->write('')
                ->string( $block->getName() )
                ->raw("\t\t=>\t[ \$this , ")
                ->string( $block->getMethodName() )
                ->raw(" ],\n");
        }

        $compiler->outdent()->writeln('];');

        $compiler
            ->outdent()
            ->writeln('}')
            ->line()
        ;

        $compiler
            ->writeln('/**')
            ->writeln(' * Render template')
            ->writeln(' * @param array $context')
            ->writeln(' */')
            ->writeln('public function display( array $context = [] , array $blocks = [] ) {')
            ->indent()
        ;

        if ( $this->hasParent() ) {

            foreach ( $this->getBody()->getNodes() as $node ) {
                if ( !$node instanceof PrintNode ) {
                    $node->compile( $compiler );
                }
            }

            $compiler
                ->writeln('$this->parent->display( $context , array_merge( $this->blocks , $blocks ) );');
        } else {
            $this->getBody()->compile($compiler);
        }

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

    private function hasParent() {
        return $this->hasNode( self::NODE_PARENT );
    }

    private function renderParentFunction( Compiler $compiler)
    {

        if ( $this->hasParent() ) {

            $parent = $this->getNode( self::NODE_PARENT );

            $compiler
                ->writeln('// Line ' . $parent->getLineNo() )
                ->writeln('protected function getParent() {')->indent()
                ->write( 'return ' )->subcompile( $parent )->raw(';')->line()
                ->outdent()->writeln('}')
                ->line()
            ;
        }

    }

}