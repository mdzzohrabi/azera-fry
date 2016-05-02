<?php
/**
 * Created by PhpStorm.
 * User: Masoud
 * Date: 18/04/2016
 * Time: 03:20 PM
 */

namespace Azera\Fry\Extension\Core;


use Azera\Fry\Compiler;
use Azera\Fry\Node;

class SandboxNode extends Node
{

    /**
     * @var Node
     */
    private $body;

    /**
     * SandboxNode constructor.
     * @param Node $body
     * @param int  $lineNo
     */
    function __construct(Node $body , $lineNo)
    {
        parent::__construct( [] , [], $lineNo);
        $this->body = $body;
        $this->lineNo = $lineNo;
    }

    function compile(Compiler $compiler)
    {

        $compiler->writeln('$_sandbox = $context;');
        $compiler->subcompile( $this->body );
        $compiler->writeln('$context = $_sandbox;');

    }

}