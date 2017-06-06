<?php

namespace Mofab\MetricBundle\Tests;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;

abstract class WebTest extends KernelTestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ContainerMock
     */
    protected $container;

    /**
     * @var Container
     */
    protected static $staticContainer;

    /**
     *
     */
    protected function setUp()
    {
        if (is_null(static::$staticContainer)) {
            static::bootKernel();
            static::$staticContainer = static::$kernel->getContainer();
        }
        $this->container = new ContainerMock(static::$staticContainer);
        $this->em = $this->container->get('doctrine');
    }

    /**
     *
     */
    protected function initContainer()
    {
        static::$staticContainer = null;
        self::setUp();
    }

    /**
     *
     */
    public function tearDown()
    {
        if (!is_null($this->container)) {
            $this->container->restoreOriginals();
        }
        parent::tearDown();
    }
}
