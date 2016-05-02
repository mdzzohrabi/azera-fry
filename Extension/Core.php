<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Extension;


use Azera\Fry\Extension\Core\SandboxParser;
use Azera\Fry\AbstractExtension;
use Azera\Fry\Core\Filter;
use Azera\Fry\Core\Operator;
use Azera\Fry\Node\Expression\Range;

class Core extends AbstractExtension
{

    public function getSectionParsers()
    {
        return array(
            new SandboxParser()
        );
    }

    public function getFilters()
    {
        return array(

            // String filters
            new Filter( 'lower' , 'strtolower' ),
            new Filter( 'upper' , 'strtoupper' ),
            new Filter( 'trim' , 'trim' ),
            new Filter( 'nl2br' , 'nl2br' ),

            // Array filters
            new Filter( 'merge' , [ $this , 'filterMerge' ] ),
            new Filter( 'join' , [ $this , 'filterJoin' ] ),
            new Filter( 'split' , [ $this , 'filterSplit' ] ),

        );
    }

    public function getBinaryOperators()
    {
        return array(
            new Operator( '..' , 5 , Range::class ),
            new Operator( '>' , 10 ),
            new Operator( '<' , 10 ),
            new Operator( '<=' , 10 ),
            new Operator( '>=' , 10 ),
            new Operator( '==' , 10 ),
            new Operator( 'equals' , 10 ),
            new Operator( '+' , 20 ),
            new Operator( '~' , 20 ),
            new Operator( '-' , 20 ),
            new Operator( '/' , 60 ),
            new Operator( '*' , 60 ),
        );
    }

    public function filterMerge( $src, $dest ) {
        return array_merge( (array)$src , (array)$dest );
    }

    public function filterJoin( $array , $by ) {
        return implode( $by , (array)$array );
    }

    public function filterSplit( $src , $by ) {
        return explode( $by , $src );
    }

}