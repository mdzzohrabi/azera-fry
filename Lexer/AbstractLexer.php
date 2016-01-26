<?php
namespace Azera\Fry\Lexer;

abstract class AbstractLexer {

    public abstract function canHandle( Reader $reader );

    public abstract function lex( Reader $reader , Tokens $tokens );

}
 ?>
