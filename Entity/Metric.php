<?php

namespace Mofab\MetricBundle\Entity;

class Metric
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $application;

    /**
     * @var string
     */
    private $script;

    /**
     * @var string
     */
    private $unit;

    /**
     * @var integer
     */
    private $quantity;

    /**
     * @var integer
     */
    private $memory;

    /**
     * @var boolean
     */
    private $completed;

    /**
     * @var float
     */
    private $added;

    /**
     * @var float
     */
    private $updated;

    /**
     *
     */
    public function __construct()
    {
        $this->quantity = 0;
        $this->memory = 0;
        $this->completed = false;
        $this->added = microtime(true);
        $this->updated = microtime(true);
    }

    /**
     *
     */
    public function preUpdate()
    {
        $this->updated = microtime(true);
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $application
     * @return Metric
     */
    public function setApplication($application)
    {
        $this->application = $application;
        return $this;
    }

    /**
     * @return string
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param string $script
     * @return Metric
     */
    public function setScript($script)
    {
        $this->script = $script;
        return $this;
    }

    /**
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * @param string $unit
     * @return Metric
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param integer $quantity
     * @return Metric
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }


    /**
     * @param integer $memory
     * @return Metric
     */
    public function setMemory($memory)
    {
        $this->memory = $memory;
        return $this;
    }

    /**
     * @return integer
     */
    public function getMemory()
    {
        return $this->memory;
    }

    /**
     * @param boolean $completed
     * @return Metric
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isCompleted()
    {
        return $this->completed;
    }

    /**
     * @param float $added
     * @return Metric
     */
    public function setAdded($added)
    {
        $this->added = $added;
        return $this;
    }

    /**
     * @return float
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @param float $updated
     * @return Metric
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }

    /**
     * @return float
     */
    public function getUpdated()
    {
        return $this->updated;
    }
}
