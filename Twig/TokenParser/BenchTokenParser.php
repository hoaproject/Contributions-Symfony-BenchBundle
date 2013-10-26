<?php
namespace Hoathis\Bundle\BenchBundle\Twig\TokenParser;

use Hoathis\Bundle\BenchBundle\Twig\Node\BenchNode;

class BenchTokenParser extends \Twig_TokenParser
{
    protected $benchIsAvailable;

    public function __construct($benchIsAvailable)
    {
        $this->benchIsAvailable = $benchIsAvailable;
    }

    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $name = $this->parser->getExpressionParser()->parseExpression();

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse(array($this, 'decideBenchEnd'), true);
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        if ($this->benchIsAvailable) {
            return new BenchNode($name, $body, new \Twig_Node_Expression_AssignName($this->parser->getVarName(), $token->getLine()), $lineno, $this->getTag());
        }

        return $body;
    }

    public function decideBenchEnd(\Twig_Token $token)
    {
        return $token->test('benchstop');
    }

    public function getTag()
    {
        return 'benchstart';
    }
} 