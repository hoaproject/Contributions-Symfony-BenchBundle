<?php
namespace Hoathis\Bundle\BenchBundle\Twig\Extension;

use Hoa\Bench\Bench;
use Hoathis\Bundle\BenchBundle\Twig\TokenParser\BenchTokenParser;

class BenchExtension extends \Twig_Extension
{
    const NAME = 'bench';

    protected $bench;
    protected $tokenParsersFactory;

    public function __construct(Bench $bench = null)
    {
        $this
            ->setBench($bench)
            ->setTokenParsersFactory()
        ;
    }

    public function setBench(Bench $bench = null)
    {
        $this->bench = $bench;

        return $this;
    }

    public function getBench()
    {
        return $this->bench;
    }

    public function setTokenParsersFactory($factory = null)
    {
        $bench = $this->bench;
        $factory = $factory ?: function() use ($bench) {
            return array(
                new BenchTokenParser($bench !== null)
            );
        };

        if (false === is_callable($factory)) {
            throw new \InvalidArgumentException('Argument is not callable');
        }

        $this->tokenParsersFactory = $factory;

        return $this;
    }

    public function getTokenParsers()
    {
        return call_user_func($this->tokenParsersFactory);
    }

    public function getName()
    {
        return self::NAME;
    }
}
