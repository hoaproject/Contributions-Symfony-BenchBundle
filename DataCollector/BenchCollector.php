<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hoathis\Bundle\BenchBundle\DataCollector;

use Hoa\Bench\Bench;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class BenchCollector implements DataCollectorInterface
{
    protected $bench;
    protected $data = array();

    public function __construct(Bench $bench)
    {
        $this->bench = $bench;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $data = array(
            'nb_marks' => count($this->bench),
            'total_time' => 0,
            'marks' => array(),
            'longest' => $this->bench->getLongest()
        );

        foreach ($this->bench->getStatistic() as $id => $mark) {
            $data['marks'][] = array(
                'id' => $id,
                'time' => $mark[Bench::STAT_RESULT],
                'percent' => $mark[Bench::STAT_POURCENT],
            );

            $data['total_time'] += $mark[Bench::STAT_RESULT];
        }

        $this->data = array($this->getName() => $data);
    }

    public function getName()
    {
        return 'bench';
    }

    public function getNbMarks() {
        return $this->data[$this->getName()]['nb_marks'];
    }

    public function getTotalTime() {
        return $this->data[$this->getName()]['total_time'];
    }

    public function getMarks() {
        return $this->data[$this->getName()]['marks'];
    }

    public function getLongest() {
        return $this->data[$this->getName()]['longest'];
    }
}
