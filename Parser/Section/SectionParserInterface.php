<?php
/**
 * Created by PhpStorm.
 * User: Masoud
 * Date: 18/04/2016
 * Time: 05:05 PM
 */

namespace Azera\Fry\Parser\Section;

use Azera\Fry\Parser\ParserInterface;

/**
 * Interface SectionParserInterface
 * @package Azera\Parser\Section
 */
interface SectionParserInterface extends ParserInterface
{

    /**
     * @return string
     */
    public function getSectionName();

    /**
     * @return string
     */
    public function getSectionEnd();

    /**
     * @return boolean
     */
    public function allowBrace();

}