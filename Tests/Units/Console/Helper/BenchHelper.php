<?php

namespace Hoathis\Bundle\BenchBundle\Tests\Units\Console\Helper;

use atoum;
use atoum\mock\controller;
use atoum\test\adapter\call;
use Hoa\Bench\Bench;
use Hoathis\Bundle\BenchBundle\Console\Helper\BenchHelper as TestedClass;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Output\NullOutput;

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
                ->object($helper->getTable())->isInstanceOf(
                    class_exists('Symfony\Component\Console\Helper\Table')
                        ? 'Symfony\Component\Console\Helper\Table'
                        : 'Symfony\Component\Console\Helper\TableHelper'
                )
            ->given($bench = new Bench())
            ->if($helper = new TestedClass($bench))
            ->then
                ->object($helper->getBench())->isIdenticalTo($bench)
            ->given($table = class_exists('Symfony\Component\Console\Helper\Table') ? new Table(new NullOutput()) : new TableHelper())
            ->if($helper = new TestedClass($bench, $table))
            ->then
                ->object($helper->getTable())->isIdenticalTo($table)
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
            ->given($output = new \mock\Symfony\Component\Console\Output\NullOutput())
            ->and(
                $table = class_exists('Symfony\Component\Console\Helper\Table')
                    ? new \mock\Symfony\Component\Console\Helper\Table($output)
                    : new \mock\Symfony\Component\Console\Helper\TableHelper()
            )
            ->if($helper = new \mock\Hoathis\Bundle\BenchBundle\Console\Helper\BenchHelper(null, $table))
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
                ->sizeof($calls = $table->getMockController()->getCalls(new call('addRow'))->toArray())
                    ->isEqualTo(4)
                    ->foreach(array_slice($calls, 1), function($test, $call) use ($marks, & $i) {
                        $i = $i ?: 0;
                        $arguments = $call->getArguments();

                        $test
                            ->array($arguments[0])
                                ->string[0]->isEqualTo($marks[$i++])
                                ->float[1]->isGreaterThan(0.0)
                                ->float[2]->isGreaterThan(0.0)->isLessThanOrEqualTo(100.0)
                        ;
                    })
            ;

            if (class_exists('Symfony\Component\Console\Helper\Table')) {
                $this
                    ->mock($table)
                       ->call('render')->withArguments()->once()
                ;
            } else {
                $this
                    ->mock($table)
                       ->call('render')->withArguments($output)->once()
                ;
            }

            $this->mock($output)
                ->call('write')->atLeastOnce()
                ->call('writeln')->atLeastOnce()
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
