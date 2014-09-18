<?php

namespace Hoathis\Bundle\BenchBundle\Console\Helper;

use Hoa\Bench\Bench;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class BenchHelper extends Helper
{
    const NAME = 'bench';

    protected $bench;
    protected $table;

    public function __construct(Bench $bench = null, $table = null)
    {
        $this
            ->setBench($bench)
            ->setTable($table)
        ;

        $this->table->setHeaders(
            array(
                'Mark',
                'Time',
                'Percent'
            )
        );
    }

    public function setTable($table = null)
    {
        if (null === $table) {
            $table = class_exists('Symfony\Component\Console\Helper\Table') ? new Table(new NullOutput()) : new TableHelper();
        }

        if (
            false === is_object($table) &&
            false === ($table instanceof TableHelper) &&
            (
                class_exists('Symfony\Component\Console\Helper\Table') &&
                false === ($table instanceof Table)
            )
        ) {
            throw new \InvalidArgumentException(sprintf(
                'Argument 1 passed to %s must be an instanceof Symfony\Component\Console\Helper\Table or Symfony\Component\Console\Helper\TableHelper, %s given',
                __METHOD__,
                is_object($table) ? get_class($table) : gettype($table)
            ));
        }

        $this->table = $table;

        return $this;
    }

    public function getTable()
    {
        return $this->table;
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
            $this->table->addRow(
                array(
                    $id,
                    $mark[Bench::STAT_RESULT],
                    $mark[Bench::STAT_POURCENT]
                )
            );
        }

        if ($this->table instanceof Table) {
            try {
                $p = new \ReflectionProperty($this->table, 'output');
                $p->setAccessible(true);
                $p->setValue($this->table, $output);
            } catch(\ReflectionException $e) {}

            $this->table->render();
        } else {
            $this->table->render($output);
        }

        return $this;
    }

    public function getName()
    {
        return self::NAME;
    }
}
