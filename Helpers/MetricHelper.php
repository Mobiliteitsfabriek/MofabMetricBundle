<?php

namespace Mofab\MetricBundle\Helpers;

use Mofab\MetricBundle\Entity\Metric;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class MetricHelper
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var Logger|LoggerInterface
     */
    private $logger;

    /**
     * @var ArrayCollection
     */
    private $metrics;

    /**
     * @var string
     */
    private $application;

    /**
     * @var bool
     */
    private $doMetrics = true;

    /**
     * @param Registry $registry
     * @param Logger $logger
     */
    public function __construct(Registry $registry, LoggerInterface $logger)
    {
        $this->doctrine = $registry;
        $this->logger = $logger;

        $this->metrics = new ArrayCollection();
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->doMetrics) {
            foreach ($this->metrics as $metric) {
                $metric->setMemory(memory_get_peak_usage(true))->setCompleted(true);
                self::save($metric);
            }
        }
    }

    /**
     * @param string $application
     */
    public function setApplication($application)
    {
        $this->application = $application;
    }

    /**
     * @param string $script
     * @param string $unit
     */
    public function start($script, $unit)
    {
        if ($this->doMetrics) {
            foreach ($this->metrics as $metric) {
                if ($metric->getScript() == $script) {
                    $this->logger->addError(
                        'Trying to start metric that is already started.' .
                        ' Script: ' . $metric->getScript() .
                        ' Unit/quantity: ' . $metric->getUnit() . '/' . $metric->getQuantity() .
                        ' Memory: ' . $metric->getMemory()
                    );
                    return;
                }
            }
            $metric = new Metric();
            $metric->setApplication($this->application)->setUnit($unit)->setScript($script);
            $this->doctrine->getManager()->persist($metric);
            $this->save($metric);
            $this->metrics->add($metric);
        }
    }

    /**
     * @param int $quantity
     * @param string|null $script
     */
    public function updateQuantity($quantity, $script = null)
    {
        if ($this->doMetrics) {
            $metric = $this->getMetric($script);
            if (is_null($metric)) {
                $this->logger->addError(
                    'Script "' . $script . '" is trying to update quantity to ' . $quantity . ' on non existing metric'
                );
                return;
            }
            $metric->setQuantity($quantity)->setMemory(memory_get_peak_usage(true));
            $this->save($metric);
        }
    }

    /**
     * @param int $quantity
     * @param string|null $script
     */
    public function increaseQuantity($quantity, $script = null)
    {
        if ($this->doMetrics) {
            $metric = self::getMetric($script);
            if (is_null($metric)) {
                $this->logger->addError(
                    'Script "' . $script . '" is trying to increase quantity with ' . $quantity .
                    ' on non existing metric'
                );
                return;
            }
            $metric->setQuantity($metric->getQuantity() + $quantity)->setMemory(memory_get_peak_usage(true));
            $this->save($metric);
        }
    }

    /**
     * @param string $script
     * @param int|null $quantity
     */
    public function complete($script, $quantity = null)
    {
        if ($this->doMetrics) {
            $metric = $this->getMetric($script);
            if (is_null($metric)) {
                $this->logger->addError('Script "' . $script . '" is trying to complete non existing metric');
                return;
            }

            if (!is_null($quantity)) {
                $metric->setQuantity($quantity);
            }
            $metric->setMemory(memory_get_peak_usage(true))->setCompleted(true);
            $this->save($metric);
            $this->metrics->removeElement($metric);
        }
    }

    /**
     * @param string|null $script
     * @return Metric|null
     */
    public function getMetric($script = null)
    {
        foreach ($this->metrics as $metric) {
            if ($metric->getScript() == $script) {
                return $metric;
            }
        }
        if (is_null($script) && $this->metrics->count() > 0) {
            return $this->metrics->first();
        }
        return null;
    }

    /**
     * @param Metric $metric
     */
    private function save(Metric $metric)
    {
        if ($this->doctrine->getManager()->isOpen()) {
            $metric = $this->doctrine->getManager()->merge($metric);
            $this->doctrine->getManager()->flush($metric);
        } else {
            $this->logger->addCritical(
                'Trying to save metric but the entity manager is closed.' .
                ' Script: ' . $metric->getScript() .
                ' Unit/quantity: ' . $metric->getUnit() . '/' . $metric->getQuantity() .
                ' Memory: ' . $metric->getMemory()
            );
        }
    }

    /**
     *
     */
    public function disable()
    {
        $this->doMetrics = false;
    }

    /**
     *
     */
    public function enable()
    {
        $this->doMetrics = true;
    }
}
