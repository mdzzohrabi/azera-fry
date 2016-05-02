<?php
/**
 * (c) Masoud Zohrabi <mdzzohrabi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azera\Fry\Parser;

use Azera\Fry\Environment;
use Azera\Fry\Exception\SyntaxException;
use Azera\Fry\Node;
use Azera\Fry\Parser;
use Azera\Fry\Token;
use Azera\Fry\TokenTypes;

/**
 * Class SectionParser
 *
 * @package Azera\Fry\Parser
 * @author  Masoud Zohrabi <mdzzohrabi@gmail.com>
 */
class SectionParser implements ParserInterface
{

    /**
     * @var ParserInterface[]
     */
    protected $sections;
    /**
     * @var Environment
     */
    private $environment;

    public function __construct( Environment $environment = null )
    {
        $this->sections = [
            new Parser\Section\IfParser(),
            new Parser\Section\BlockParser(),
            new Parser\Section\LoopParser(),
            new Parser\Section\SetParser(),
            new Parser\Section\MacroParser(),
            new Parser\Section\ExtendsParser()
        ];

        if ( $environment )
            $this->sections = array_merge( $this->sections , $environment->getSectionParsers() );

        $this->environment = $environment;

    }

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return bool
     */
    public function canParse(Token $token, Parser $parser)
    {
        return $token->is( TokenTypes::T_SECTION_START );
    }

    /**
     * @param Token  $token
     * @param Parser $parser
     * @return Node
     * @throws SyntaxException
     */
    public function parse(Token $token, Parser $parser)
    {

        $stream = $parser->getStream();
        $stream->expect( TokenTypes::T_SECTION_START );
        $token = $stream->getCurrent();

        foreach ( $this->sections as $section ) {
            if ( $section->canParse( $token , $parser ) )
                return $section->parse( $token , $parser );
        }

        throw new SyntaxException( 'Unexpected section, "%s" not defined.' , $token->getValue() );

    }

}