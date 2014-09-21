<?php

namespace Hoathis\Bundle\BenchBundle\DataCollector;

use Hoa\Bench\Bench;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;

class BenchCollector implements DataCollectorInterface
{
    const NAME = 'hoathis.bench';

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
                    'global' => array(),
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
            if ($id === '__global__') {
                $this->data[self::NAME]['marks']['global'] = array(
                    'id' => $id,
                    'time' => $mark[Bench::STAT_RESULT],
                    'percent' => $mark[Bench::STAT_POURCENT],
                    'running' => $this->bench->{$id}->isRunning(),
                    'paused' => $this->bench->{$id}->isPause()
                );
            } else {
                $type = preg_match('/^twig\./', $id) === 1 ? 'twig' : 'php';
                $mark = array(
                    'id' => preg_replace('/^twig\./', '', $id),
                    'time' => $mark[Bench::STAT_RESULT],
                    'percent' => $mark[Bench::STAT_POURCENT],
                    'running' => $this->bench->{$id}->isRunning(),
                    'paused' => $this->bench->{$id}->isPause()
                );

                $this->data[self::NAME]['marks'][$type][] = $mark;
                $this->data[self::NAME]['nb_marks'] = count($this->bench);

                if ($mark['time'] > $this->data[self::NAME]['longest']['time']) {
                    $this->data[self::NAME]['longest'] = $mark;
                }
            }
        }
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

    public function formatTime($time)
    {
        if ($time < 1) {
            return sprintf('%.2f ms', $time * 1000);
        } else {
            return sprintf('%.2f s', $time);
        }
    }
}
