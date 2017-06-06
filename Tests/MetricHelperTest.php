<?php

namespace Mofab\MetricBundle\Tests\Helpers;

use Mofab\MetricBundle\Helpers\MetricHelper;
use Mofab\MetricBundle\Tests\WebTest;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;

class MetricHelperTest extends WebTest
{
    /**
     * @var MetricHelper
     */
    private $object;

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::__construct()
     */
    public function testConstruct()
    {
        $doMetricsProperty = new \ReflectionProperty(MetricHelper::class, 'doMetrics');
        $doMetricsProperty->setAccessible(true);

        $this->object = new MetricHelper($this->container->get('doctrine'), new Logger('test'));
        $this->assertTrue($this->object instanceof MetricHelper);
    }

    /**
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::setApplication()
     */
    public function testSetApplication()
    {
        $applicationProperty = new \ReflectionProperty(MetricHelper::class, 'application');
        $applicationProperty->setAccessible(true);

        $this->object = new MetricHelper($this->container->get('doctrine'), new Logger('test'));
        $this->object->setApplication('myApplication');
        $this->assertSame('myApplication', $applicationProperty->getValue($this->object));
    }

    /**
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::updateQuantity()
     */
    public function testUpdateQuantityFail()
    {
        $this->setupMocks(1, 2, false);

        $loggerMock = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
        $loggerMock->expects($this->at(0))
            ->method('addError')
            ->with('Script "" is trying to update quantity to 10 on non existing metric');
        $loggerMock->expects($this->at(1))
            ->method('addError')
            ->with('Script "myScript2" is trying to update quantity to 15 on non existing metric');

        $this->object = new MetricHelper($this->container->get('doctrine'), $loggerMock);

        $this->object->updateQuantity(10);
        $this->object->start('myScript', 'myUnit');
        $this->object->updateQuantity(15, 'myScript2');

        unset($this->object);
    }

    /**
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::increaseQuantity()
     */
    public function testIncreaseQuantityFail()
    {
        $this->setupMocks(1, 2, false);

        $loggerMock = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
        $loggerMock->expects($this->at(0))
            ->method('addError')
            ->with('Script "" is trying to increase quantity with 55 on non existing metric');
        $loggerMock->expects($this->at(1))
            ->method('addError')
            ->with('Script "myScript2" is trying to increase quantity with 547 on non existing metric');

        $this->object = new MetricHelper($this->container->get('doctrine'), $loggerMock);

        $this->object->increaseQuantity(55);
        $this->object->start('myScript', 'myUnit');
        $this->object->increaseQuantity(547, 'myScript2');

        unset($this->object);
    }

    /**
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::complete()
     */
    public function testCompleteFail()
    {
        $this->setupMocks(1, 2, false);

        $loggerMock = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
        $loggerMock->expects($this->at(0))
            ->method('addError')
            ->with('Script "myScript" is trying to complete non existing metric');
        $loggerMock->expects($this->at(1))
            ->method('addError')
            ->with('Script "myScript2" is trying to complete non existing metric');

        $this->object = new MetricHelper($this->container->get('doctrine'), $loggerMock);

        $this->object->complete('myScript');
        $this->object->start('myScript', 'myUnit');
        $this->object->complete('myScript2');

        unset($this->object);
    }

    /**
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::start()
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::updateQuantity()
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::increaseQuantity()
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::complete()
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::getMetric()
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::save()
     */
    public function testCompleteCycleWithComplete()
    {
        $this->setupMocks(1, 6, false);

        $this->object = new MetricHelper($this->container->get('doctrine'), $this->container->get('logger'));
        $this->object->setApplication('myApplication');

        $this->object->start('myScript', 'myUnit');
        $this->assertSame('myApplication', $this->object->getMetric()->getApplication());
        $this->assertSame('myScript', $this->object->getMetric()->getScript());
        $this->assertSame('myUnit', $this->object->getMetric()->getUnit());
        $this->assertSame(0, $this->object->getMetric()->getQuantity());

        $this->object->increaseQuantity(2);
        $this->assertSame(2, $this->object->getMetric()->getQuantity());

        $this->object->increaseQuantity(4);
        $this->assertSame(6, $this->object->getMetric()->getQuantity());

        $this->object->updateQuantity(3);
        $this->assertSame(3, $this->object->getMetric()->getQuantity());

        $this->object->increaseQuantity(7);
        $this->assertSame(10, $this->object->getMetric()->getQuantity());

        $this->assertSame('myApplication', $this->object->getMetric()->getApplication());
        $this->assertSame('myScript', $this->object->getMetric()->getScript());
        $this->assertSame('myUnit', $this->object->getMetric()->getUnit());

        $metric = $this->object->getMetric();
        $this->object->complete('myScript', 4);
        $this->assertSame(4, $metric->getQuantity());

        unset($this->object);
    }

    /**
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::start()
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::__destruct()
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::getMetric()
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::save()
     */
    public function testCompleteCycleWithDestruct()
    {
        $this->setupMocks(1, 2, true);

        $this->object = new MetricHelper($this->container->get('doctrine'), $this->container->get('logger'));
        $this->object->setApplication('myApplication');

        $this->object->start('myScript', 'myUnit');
        $this->assertSame('myApplication', $this->object->getMetric()->getApplication());
        $this->assertSame('myScript', $this->object->getMetric()->getScript());
        $this->assertSame('myUnit', $this->object->getMetric()->getUnit());
        $this->assertSame(0, $this->object->getMetric()->getQuantity());

        $metric = $this->object->getMetric();
        unset($this->object);

        $this->assertSame('myApplication', $metric->getApplication());
        $this->assertSame('myScript', $metric->getScript());
        $this->assertSame('myUnit', $metric->getUnit());
        $this->assertSame(0, $metric->getQuantity());
        $this->assertTrue($metric->isCompleted());

        unset($this->object);
    }

    /**
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::start()
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::save()
     */
    public function testWithSecondIdenticalMetric()
    {
        $this->setupMocks(1, 2, false);
        $loggerMock = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
        $loggerMock->expects($this->once())
            ->method('addError')
            ->with(
                'Trying to start metric that is already started. Script: myScript Unit/quantity: myUnit/0 Memory: 0'
            );

        $this->object = new MetricHelper($this->container->get('doctrine'), $loggerMock);
        $this->object->setApplication('myApplication');

        $this->object->start('myScript', 'myUnit');
        $this->assertSame('myApplication', $this->object->getMetric()->getApplication());
        $this->assertSame('myScript', $this->object->getMetric()->getScript());
        $this->assertSame('myUnit', $this->object->getMetric()->getUnit());
        $this->assertSame(0, $this->object->getMetric()->getQuantity());

        $this->object->start('myScript', 'myUnit');

        unset($this->object);
    }

    /**
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::start()
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::save()
     */
    public function testWithSecondDifferentMetric()
    {
        $this->setupMocks(2, 4, false);
        $loggerMock = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
        $loggerMock->expects($this->never())->method('addError');

        $this->object = new MetricHelper($this->container->get('doctrine'), $loggerMock);
        $this->object->setApplication('myApplication');

        $this->object->start('myScript', 'myUnit');
        $this->assertSame('myApplication', $this->object->getMetric()->getApplication());
        $this->assertSame('myScript', $this->object->getMetric()->getScript());
        $this->assertSame('myUnit', $this->object->getMetric()->getUnit());
        $this->assertSame(0, $this->object->getMetric()->getQuantity());

        $this->object->start('myScript2', 'myUnit');
        $this->assertSame('myApplication', $this->object->getMetric('myScript2')->getApplication());
        $this->assertSame('myScript2', $this->object->getMetric('myScript2')->getScript());
        $this->assertSame('myUnit', $this->object->getMetric('myScript2')->getUnit());
        $this->assertSame(0, $this->object->getMetric('myScript2')->getQuantity());

        unset($this->object);
    }

    /**
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::enable()
     * @covers \Mofab\MetricBundle\Helpers\MetricHelper::disable()
     */
    public function testEnableDisable()
    {
        $doMetricsProperty = new \ReflectionProperty(MetricHelper::class, 'doMetrics');
        $doMetricsProperty->setAccessible(true);

        $this->object = new MetricHelper($this->container->get('doctrine'), new Logger('test'));

        $this->object->enable();
        $this->assertTrue($doMetricsProperty->getValue($this->object));
        $this->object->disable();
        $this->assertFalse($doMetricsProperty->getValue($this->object));

        unset($this->object);
    }

    /**
     * @param int $nrOfPersists
     * @param int $nrOfFlushes
     * @param bool $notOpen
     */
    private function setupMocks($nrOfPersists, $nrOfFlushes, $notOpen)
    {
        $entityManagerMock = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $entityManagerMock->expects($this->exactly($nrOfPersists))->method('persist');
        if ($notOpen) {
            $entityManagerMock->expects($this->exactly($nrOfFlushes))
                ->method('isOpen')
                ->will($this->returnValue(false));

            $loggerMock = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
            $loggerMock->expects($this->exactly($nrOfFlushes))->method('addCritical');
            $this->container->set('logger', $loggerMock);
        } else {
            $entityManagerMock->expects($this->any())->method('isOpen')->will($this->returnValue(true));
            $entityManagerMock->expects($this->exactly($nrOfFlushes))->method('merge')->will($this->returnArgument(0));
            $entityManagerMock->expects($this->exactly($nrOfFlushes))->method('flush');
        }

        $doctrineMock = $this->getMockBuilder(Registry::class)->disableOriginalConstructor()->getMock();
        $doctrineMock->expects($this->any())->method('getManager')->will($this->returnValue($entityManagerMock));
        $this->container->set('doctrine', $doctrineMock);
    }
}
