<?php

namespace Hoathis\Bundle\BenchBundle\Tests\Units\Console\Helper;

use atoum;
use atoum\mock\controller;
use atoum\test\adapter\call;
use Hoa\Bench\Bench;
use Hoathis\Bundle\BenchBundle\Console\Helper\BenchHelper as TestedClass;

class BenchHelper extends atoum
{
	public function testClass()
    {
        $this
            ->testedClass
                ->isSubclassOf('Symfony\Component\Console\Helper\Helper')
            ->string(TestedClass::NAME)->isEqualTo('bench')
        ;
    }

    public function test__construct()
	{
        $this
            ->if($helper = new TestedClass())
            ->then
                ->object($helper->getBench())->isInstanceOf('Hoa\Bench\Bench')
            ->given($bench = new Bench())
            ->if($helper = new TestedClass($bench))
            ->then
                ->object($helper->getBench())->isIdenticalTo($bench)
        ;
	}

	public function testSetBench()
	{
        $this
            ->if($helper = new TestedClass())
            ->then
                ->object($helper->setBench())->isIdenticalTo($helper)
                ->object($helper->getBench())->isInstanceOf('Hoa\Bench\Bench')
            ->given($bench = new Bench())
            ->if($helper->setBench($bench))
            ->then
                ->object($helper->getBench())->isIdenticalTo($bench)
        ;
	}

	public function testStart()
	{
        $this
            ->given($bench = new \mock\Hoa\Bench\Bench())
            ->and($mark = new \mock\Hoa\Bench\Mark($name = uniqid()))
            ->and($this->calling($bench)->__get = $mark)
            ->if($helper = new TestedClass($bench))
            ->then
                ->object($helper->start($name))->isIdenticalTo($helper)
                ->mock($bench)
                    ->call('__get')->withArguments($name)->once()
                ->mock($mark)
                    ->call('start')->withoutAnyArgument()->once()
        ;
	}

	public function testPause()
	{
        $this
            ->given($bench = new \mock\Hoa\Bench\Bench())
            ->and($mark = new \mock\Hoa\Bench\Mark($name = uniqid()))
            ->and($this->calling($bench)->__get = $mark)
            ->if($helper = new TestedClass($bench))
            ->and($helper->start($name))
            ->then
                ->object($helper->pause($name))->isIdenticalTo($helper)
                ->mock($bench)
                    ->call('__get')->withArguments($name)->twice()
                ->mock($mark)
                    ->call('pause')->withArguments(false)->once()
        ;
	}

	public function testStop()
	{
        $this
            ->given($bench = new \mock\Hoa\Bench\Bench())
            ->and($mark = new \mock\Hoa\Bench\Mark($name = uniqid()))
            ->and($this->calling($bench)->__get = $mark)
            ->if($helper = new TestedClass($bench))
            ->and($helper->start($name))
            ->then
                ->object($helper->stop($name))->isIdenticalTo($helper)
                ->mock($bench)
                    ->call('__get')->withArguments($name)->twice()
                ->mock($mark)
                    ->call('stop')->withArguments(false)->once()
        ;
	}

    public function testGet()
	{
        $this
            ->given($bench = new \mock\Hoa\Bench\Bench())
            ->and($mark = new \mock\Hoa\Bench\Mark($name = uniqid()))
            ->and($this->calling($bench)->__get = $mark)
            ->if($helper = new TestedClass($bench))
            ->then
                ->object($helper->get($name))->isIdenticalTo($mark)
        ;
	}

	public function testSummarize()
	{
        $this
            ->given($output = new \mock\Symfony\Component\Console\Output\OutputInterface())
            ->if($helper = new \mock\Hoathis\Bundle\BenchBundle\Console\Helper\BenchHelper())
            ->and(
                $helper
                    ->start($marks[] = uniqid())
                    ->start($marks[] = uniqid())
                    ->start($marks[] = uniqid())
                    ->stop($marks[2])
                    ->stop($marks[1])
                    ->stop($marks[0])
            )
            ->then
                ->object($helper->summarize($output))->isIdenticalTo($helper)
                ->sizeof($calls = $helper->getMockController()->getCalls(new call('addRow'))->toArray())
                    ->isEqualTo(3)
                    ->foreach($calls, function($test, $call) use ($marks, & $i) {
                        $i = $i ?: 0;
                        $arguments = $call->getArguments();

                        $test
                            ->array($arguments[0])
                                ->string[0]->isEqualTo($marks[$i++])
                                ->float[1]->isGreaterThan(0.0)
                                ->float[2]->isGreaterThan(0.0)->isLessThanOrEqualTo(100.0)
                        ;
                    })
                ->mock($helper)
                    ->call('render')->withArguments($output)->once()
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
