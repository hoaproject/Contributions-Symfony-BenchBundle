<?php

namespace Hoathis\Bundle\BenchBundle\DataCollector;

use Hoa\Bench\Bench;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;

class BenchCollector implements DataCollectorInterface
{
    const NAME = 'bench';

    protected $bench;
    protected $data = array();

    public function __construct(Bench $bench)
    {
        $this->bench = $bench;

        $this->reset();
    }

    public function getBench()
    {
        return $this->bench;
    }

    public function reset()
    {
        $this->data = array(
            self::NAME => array(
                'nb_marks' => 0,
                'marks' => array(
                    'php' => array(),
                    'twig' => array()
                ),
                'longest' => null
            )
        );
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->reset();

        foreach ($this->bench->getStatistic() as $id => $mark) {
            $type = preg_match('/^twig\./', $id) === 1 ? 'twig' : 'php';

            $this->data[self::NAME]['marks'][$type][] = array(
                'id' => preg_replace('/^twig\./', '', $id),
                'time' => $mark[Bench::STAT_RESULT],
                'percent' => $mark[Bench::STAT_POURCENT],
                'running' => $this->bench->{$id}->isRunning(),
                'paused' => $this->bench->{$id}->isPause()
            );
        }

        $this->data[self::NAME]['nb_marks'] = count($this->bench);
        $this->data[self::NAME]['longest'] = $this->bench->getLongest();
    }

    public function getName()
    {
        return self::NAME;
    }

    public function getNbMarks() {
        return $this->data[self::NAME]['nb_marks'];
    }

    public function getMarks() {
        return $this->data[self::NAME]['marks'];
    }

    public function getLongest() {
        return $this->data[self::NAME]['longest'];
    }
}
