<?php

namespace Hoathis\Bundle\BenchBundle\Tests\Units\DataCollector;

use atoum;
use Hoa\Bench\Bench;
use Hoathis\Bundle\BenchBundle\DataCollector\BenchCollector as TestedClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BenchCollector extends atoum
{
    public function testClass()
    {
        $this
            ->testedClass
                ->isSubclassOf('Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface')
            ->string(TestedClass::NAME)->isEqualTo('hoathis.bench')
        ;
    }

    public function test__construct()
	{
        $this
            ->given($bench = new Bench())
            ->if($collector = new TestedClass($bench))
            ->then
                ->string($collector->getName())->isIdenticalTo(TestedClass::NAME)
                ->object($collector->getBench())->isIdenticalTo($bench)
                ->integer($collector->getNbMarks())->isZero()
                ->array($collector->getMarks())->isEqualTo(array('php' => array(), 'twig' => array(), 'global' => array()))
                ->variable($collector->getLongest())->isNull()
        ;
	}

    public function testCollect()
    {
        $this
            ->given($request = new Request())
            ->and($response = new Response())
            ->and($bench = new Bench())
            ->if($collector = new TestedClass($bench))
            ->and($collector->collect($request, $response))
            ->then
                ->integer($collector->getNbMarks())->isZero()
                ->array($collector->getMarks())->isEqualTo(array('php' => array(), 'twig' => array(), 'global' => array()))
                ->variable($collector->getLongest())->isNull()
            ->if($name = uniqid())
            ->and($bench->{$name}->start())
            ->and($collector->collect($request, $response))
            ->then
                ->integer($collector->getNbMarks())->isEqualTo(2)
                ->array($marks = $collector->getMarks())
                    ->hasKey('global')
                    ->hasKey('php')
                    ->hasKey('twig')
                ->array($marks['twig'])->isEmpty()
                ->in($marks['global'])
                    ->string['id']->isEqualTo('__global__')
                ->in($marks['php'][0])
                    ->string['id']->isEqualTo($name)
                    ->float['time']->isGreaterThan(0.0)
                    ->float['percent']->isGreaterThan(0.0)
                    ->boolean['running']->isTrue()
                    ->boolean['paused']->isFalse()
                ->variable($collector->getLongest())->isNotNull()
            ->if($bench->{$name}->pause())
            ->and($collector->collect($request, $response))
            ->then
                ->integer($collector->getNbMarks())->isEqualTo(2)
                ->array($marks = $collector->getMarks())
                ->in($marks['php'][0])
                    ->string['id']->isEqualTo($name)
                    ->float['time']->isGreaterThan(0.0)
                    ->float['percent']->isGreaterThan(0.0)
                    ->boolean['running']->isTrue()
                    ->boolean['paused']->isTrue()
                ->variable($collector->getLongest())->isNotNull()
            ->if($bench->{$name}->stop())
            ->and($collector->collect($request, $response))
            ->then
            ->integer($collector->getNbMarks())->isEqualTo(2)
                ->array($marks = $collector->getMarks())
                ->in($marks['php'][0])
                    ->string['id']->isEqualTo($name)
                    ->float['time']->isGreaterThan(0.0)
                    ->float['percent']
                        ->isGreaterThan(0.0)
                        ->isLessThanOrEqualTo(100.0)
                    ->boolean['running']->isFalse()
                    ->boolean['paused']->isFalse()
                ->variable($collector->getLongest())->isNotNull()
            ->if($otherName = 'twig.' . $name)
            ->and($bench->{$otherName}->start()->stop())
            ->and($collector->collect($request, $response))
            ->then
                ->integer($collector->getNbMarks())->isEqualTo(3)
                ->array($marks = $collector->getMarks())
                    ->hasKey('php')
                    ->hasKey('twig')
                ->in($marks['twig'][0])
                    ->string['id']->isEqualTo($name)
                    ->float['time']->isGreaterThan(0.0)
                    ->float['percent']->isGreaterThan(0.0)
                    ->boolean['running']->isFalse()
                    ->boolean['paused']->isFalse()
                ->variable($collector->getLongest())->isNotNull()
        ;
    }
}
