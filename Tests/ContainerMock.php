<?php

namespace Mofab\MetricBundle\Tests;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class ContainerMock implements ContainerInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $originals = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $name
     * @param int $invalidBehavior
     * @return object
     */
    public function get($name, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
    {
        return $this->container->get($name, $invalidBehavior);
    }

    /**
     * @param string $name
     * @param object $object
     */
    public function set($name, $object)
    {
        try {
            $this->originals[] = [
                'name' => $name,
                'object' => $this->container->get($name),
            ];
        } catch (\Exception $e) {
            // Apparently the service does not exist yet
        }
        $this->container->set($name, $object);
    }

    /**
     *
     */
    public function restoreOriginals()
    {
        while (count($this->originals) > 0) {
            $original = array_pop($this->originals);
            $this->container->set($original['name'], $original['object']);
        }
    }

    /**
     * @param string $id The service identifier
     * @return bool true if the service is defined, false otherwise
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function has($id)
    {
        return $this->container->has($id);
    }

    /**
     * @param string $id
     * @return bool true if the service has been initialized, false otherwise
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function initialized($id)
    {
        return $this->container->initialized($id);
    }

    /**
     * @param string $name The parameter name
     * @return mixed The parameter value
     * @throws InvalidArgumentException if the parameter is not defined
     */
    public function getParameter($name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * @param string $name The parameter name
     * @return bool The presence of parameter in container
     */
    public function hasParameter($name)
    {
        return $this->container->hasParameter($name);
    }

    /**
     * @param string $name The parameter name
     * @param mixed $value The parameter value
     */
    public function setParameter($name, $value)
    {
        $this->container->setParameter($name, $value);
    }
}
