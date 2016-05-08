<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry;


use Azera\Fry\Core\NativeFunction;
use Azera\Fry\Core\Filter;
use Azera\Fry\Core\Operator;
use Azera\Fry\Core\SimpleFunction;
use Azera\Fry\Extension\Core;
use Azera\Fry\Loader\LoaderInterface;
use Azera\Fry\Parser\Section\SectionParserInterface;

/**
 * Class Environment
 * @package Azera\Fry
 */
class Environment
{

    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * Temp directory
     * @var string
     */
    protected $temp_dir;

    /**
     * @var string
     */
    protected $templateClassPrefix = '__FryTemplate__';

    /**
     * Binary operators
     * @var Operator[]
     */
    protected $binaryOperators = [];

    /**
     * Unary operators
     * @var Operator[]
     */
    protected $unaryOperators = [];

    /**
     * Functions
     * @var SimpleFunction[]|NativeFunction[]
     */
    protected $functions = [];

    /**
     * Filters
     * @var Filter[]
     */
    protected $filters = [];

    /**
     * @var SectionParserInterface[]
     */
    protected $sectionParsers = [];

    /**
     * @var object[]
     */
    protected $loadedTemplates;

    public function __construct( LoaderInterface $loader )
    {

        $this->loader = $loader;
        $this->addExtension( new Core() );

    }

    /**
     * @param AbstractExtension $extension
     * @return $this
     */
    public function addExtension( AbstractExtension $extension ) {

        foreach ( $extension->getFilters() as $filter )
            $this->filters[ $filter->getName() ] = $filter;

        foreach ( $extension->getFunctions() as $function )
            $this->functions[ $function->getName() ] = $function;

        foreach ( $extension->getBinaryOperators() as $operator )
            $this->binaryOperators[ $operator->getName() ] = $operator;

        foreach ( $extension->getUnaryOperators() as $operator )
            $this->unaryOperators[ $operator->getName() ] = $operator;

        foreach ( $extension->getSectionParsers() as $sectionParser )
            $this->sectionParsers[] = $sectionParser;

        return $this;

    }

    /**
     * @return string
     */
    public function getTempDir()
    {
        return $this->temp_dir;
    }

    /**
     * @param string $temp_dir
     */
    public function setTempDir($temp_dir)
    {
        $this->temp_dir = $temp_dir;
    }

    /**
     * @return string
     */
    public function getTemplateClassPrefix()
    {
        return $this->templateClassPrefix;
    }

    /**
     * @param string $templateClassPrefix
     */
    public function setTemplateClassPrefix($templateClassPrefix)
    {
        $this->templateClassPrefix = $templateClassPrefix;
    }

    public function getTemplateClass( $name ) {
        return $this->templateClassPrefix . $this->loader->getCacheKey( $name );
    }

    /**
     * @return LoaderInterface
     */
    public function getLoader()
    {
        return $this->loader;
    }

    public function compileSource( $source , $fileName ) {

        $lexer = new Lexer( new Reader( $source , $fileName ) , $this );
        $parser = new Parser( $lexer->tokenize()->getStream() , $this );
        $compiler = new Compiler( $this );
        $compiled = $compiler->compile( $parser->parse() );

        return $compiled;

    }

    /**
     * @return Operator[]
     */
    public function getBinaryOperators()
    {
        return $this->binaryOperators;
    }

    /**
     * @return Operator[]
     */
    public function getUnaryOperators()
    {
        return $this->unaryOperators;
    }

    /**
     * @return SimpleFunction[]
     */
    public function getFunctions()
    {
        return $this->functions;
    }


    /**
     * @param $name
     * @return NativeFunction|SimpleFunction|null
     */
    public function getFunction( $name ) {
        return $this->functions[ $name ];
    }

    /**
     * @param $name
     * @return SimpleFunction
     */
    public function hasFunction( $name ) {
        return isset( $this->functions[ $name ] );
    }

    /**
     * @return Filter[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param $name
     * @return Filter
     */
    public function getFilter( $name ) {
        return $this->filters[ $name ];
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasFilter( $name ) {
        return isset( $this->filters[ $name ] );
    }

    /**
     * @param string $template
     * @return Template
     */
    public function loadTemplate( $template ) {

        $class = $this->getTemplateClass( $template );

        if ( $this->loadedTemplates[ $class ] )
            return $this->loadedTemplates[ $class ];

        $code = $this->compileSource($this->loader->getSource($template), $template);

        if ( $this->temp_dir ) {

            if (!file_exists($this->temp_dir))
                mkdir($this->temp_dir, 0777, true);

            $tmpFile = $this->temp_dir . '/' . $this->loader->getCacheKey($template) . '.php';
            file_put_contents($tmpFile, $code);

            include_once $tmpFile;

        } else {

            eval( preg_replace( '/<\?php/A', '', $code ) );

        }

        return new $class( $this );

    }

    /**
     * @param $name
     * @return Operator
     */
    public function getBinaryOperator($name) {
        return $this->binaryOperators[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasBinaryOperator( $name ) {
        return isset($this->binaryOperators[ $name ]);
    }

    /**
     * @param $name
     * @return Operator
     */
    public function getUnaryOperator($name) {
        return $this->unaryOperators[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasUnaryOperator( $name ) {
        return isset($this->unaryOperators[ $name ]);
    }

    /**
     * @return SectionParserInterface[]
     */
    public function getSectionParsers()
    {
        return $this->sectionParsers;
    }

}