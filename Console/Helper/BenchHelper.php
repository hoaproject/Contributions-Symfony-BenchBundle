<?php

namespace Hoathis\Bundle\BenchBundle\Console\Helper;

use Hoa\Bench\Bench;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Output\OutputInterface;

class BenchHelper extends TableHelper
{
    const NAME = 'bench';

    protected $bench;

    public function __construct(Bench $bench = null)
    {
        parent::__construct();

        $this
            ->setBench($bench)
            ->setHeaders(
                array(
                    'Mark',
                    'Time',
                    'Percent'
                )
            )
        ;
    }

    public function setBench(Bench $bench = null)
    {
        $this->bench = $bench ?: new Bench();

        return $this;
    }

    public function getBench()
    {
        return $this->bench;
    }

    public function start($name)
    {
        $this->get($name)->start();

        return $this;
    }

    public function pause($name, $silent = false)
    {
        $this->get($name)->pause($silent);

        return $this;
    }

    public function stop($name, $silent = false)
    {
        $this->get($name)->stop($silent);

        return $this;
    }

    public function get($name)
    {
        return $this->bench->{$name};
    }

    public function summarize(OutputInterface $output)
    {
        foreach ($this->bench->getStatistic() as $id => $mark) {
            $this->addRow(
                array(
                    $id,
                    $mark[Bench::STAT_RESULT],
                    $mark[Bench::STAT_POURCENT]
                )
            );
        }

        $this->render($output);

        return $this;
    }

    public function getName()
    {
        return self::NAME;
    }
}
