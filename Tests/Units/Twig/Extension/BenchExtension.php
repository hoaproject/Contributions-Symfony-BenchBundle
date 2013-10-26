<?php

namespace Hoathis\Bundle\BenchBundle\Tests\Units\Twig\Extension;

use atoum;
use Hoa\Bench\Bench;
use Hoathis\Bundle\BenchBundle\Twig\Extension\BenchExtension as TestedClass;

class BenchExtension extends atoum
{
    public function testClass()
    {
        $this
            ->testedClass
                ->isSubclassOf('Twig_Extension')
            ->string(TestedClass::NAME)->isEqualTo('bench')
        ;
    }

	public function test__construct()
	{
        $this
            ->if($extension = new TestedClass())
            ->then
                ->variable($extension->getBench())->isNull()
            ->given($bench = new Bench())
            ->if($extension = new TestedClass($bench))
            ->then
                ->object($extension->getBench())->isIdenticalTo($bench)
        ;
	}

	public function testSetTokenParsersFactory()
	{
        $this
            ->if($extension = new TestedClass())
            ->then
                ->exception(function() use ($extension) {
                    $extension->setTokenParsersFactory(uniqid());
                })
                    ->isInstanceOf('InvalidArgumentException')
                    ->hasMessage('Argument is not callable')
            ->if($factory = function() {})
            ->then
                ->object($extension->setTokenParsersFactory($factory))->isIdenticalTo($extension)
        ;
	}

	public function testGetTokenParsers()
	{
        $this
            ->if($extension = new TestedClass())
            ->then
                ->array($extension->getTokenParsers())
                    ->object[0]->isInstanceOf('Hoathis\Bundle\BenchBundle\Twig\TokenParser\BenchTokenParser')
            ->given($factory = function() use (& $parser) { return array($parser = new \mock\Twig_TokenParserInterface()); })
            ->and($extension->setTokenParsersFactory($factory))
            ->array($extension->getTokenParsers())
                ->object[0]->isIdenticalTo($parser)
        ;
	}

	public function testGetName()
	{
        $this
            ->if($extension = new TestedClass())
            ->then
                ->string($extension->getName())->isEqualTo(TestedClass::NAME)
        ;
	}
}
