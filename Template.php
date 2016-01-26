<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry;


use Azera\Fry\Exception\Exception;
use LogicException;

abstract class Template
{
    const METHOD_GET = 0x001;
    const METHOD_CALL = 0x002;

    /**
     * @var Environment
     */
    private $environment;

    public function __construct( Environment $environment )
    {
        $this->environment = $environment;
    }

    public abstract function display( array $context = [] );

    public abstract function getTemplateName();

    public function render( array $context = [] ) {

        ob_start();
        $this->display( $context );
        return ob_get_clean();

    }

    /**
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    public function getValue( $context , array $route , $type = self::METHOD_GET , array $arguments = [] ) {

        if ( count($route) == 1 && $type == self::METHOD_CALL ) {
            $name = current( $route );
            if ( $this->hasMacro( $name ) )
                return $this->callMacro( $name , $arguments );
        }

        $parentNode = null;

        foreach ( $route as $name ) {

            if ( is_array( $context ) ) {

                if ( !isset( $context[ $name ] ) )
                    throw new LogicException(
                        $parentNode ?
                        sprintf('Key "%s" not found in %s', $name, $parentNode) :
                        sprintf('Variable "%s" not found', $name )
                    );

                $context = $context[ $name ];

            } elseif ( is_object( $context ) ) {

                if ( method_exists( $context , $name ) )
                    $context = call_user_func( [ $context , $name ] );

                elseif ( property_exists( $context , $name ) )
                    $context = $context->{$name};

                else
                    throw new LogicException(
                        $parentNode ?
                            sprintf('Method/Property "%s" not found in %s', $name, $parentNode) :
                            sprintf('"%s" not found', $name )
                    );

            } else
                throw new LogicException( sprintf('Invalid property access on %s', gettype($context) ) );

            $parentNode = $name;

        }

        if ( $type == self::METHOD_CALL )
            return call_user_func_array( $context , $arguments );

        return $context;

    }

    public function getReference( &$context , array $route ) {
        throw new Exception('Not implemented');
    }

    public function set( &$context , array $route , $value ) {

        $rawValue = $value();
        $var = &$context;

        foreach ( $route as $name )
            $var = &$var[ $name ];

        $var = $rawValue;

        return $rawValue;

    }

    public function hasBlock( $name ) {
        return method_exists( $this , 'block_' . $name );
    }

    public function hasMacro( $name ) {
        return method_exists( $this , 'macro_' . $name );
    }

    public function callMacro( $name , $arguments ) {
        return call_user_func_array([ $this , 'macro_' . $name], [ [] , $arguments ] );
    }

    public function renderBlock( $name , array $context = [] ) {

        if ( !$this->hasBlock( $name ) )
            throw new LogicException(sprintf('Block `%s` does not exists',$name));

        return $this->{'block_'.$name}( $context );
    }

    public function filter( $name , $arguments , $content ) {

        if ( !$this->environment->hasFilter( $name ) )
            throw new LogicException(sprintf( 'Filter "%s" not defined' , $name ));

        $filter = $this->environment->getFilter( $name );

        return call_user_func_array( $filter->getCallable() , array_merge( [ $content() ] , $arguments ) );
    }

    protected function ensureTraversable( $data ) {

        if ( !is_array( $data ) && !$data instanceof \Traversable )
            throw new LogicException(sprintf('Invalid traversable data'));

        return $data;

    }

    protected function prepareArgs( array $args , array $params ) {

        $a = [];

        foreach ( $params as $k => $v ) {
            $ok = false;
            if ( is_int( $k ) ) {
                $a[ key($args) ] = $v;
                next( $args );
                $ok = true;
            }
            else {
                if (array_key_exists( $k , $args )) {
                    $a[ $k ] = $v;
                    $ok = true;
                }
            }
            if ( $ok )
                unset( $params[$k] );
        }

        if ( !empty( $params ) )
            throw new LogicException(sprintf('Invalid arguments passed, "%s".' , implode( ', ', array_keys($params) ) ));

        return array_merge( $args , $a );

    }

}