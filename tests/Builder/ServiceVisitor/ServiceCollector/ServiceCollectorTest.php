<?php

namespace Butterfly\Component\DI\Tests\Builder\ServiceVisitor\ServiceCollector;

use Butterfly\Component\DI\Builder\ServiceCollector\ServiceCollector;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ServiceCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceCollector
     */
    protected $collector;

    protected function setUp()
    {
        $this->collector = new ServiceCollector();
    }

    public function testVisit()
    {
        $this->collector->visit('service1', array(
            'class'     => 'A',
            'arguments' => array(1, 2),
            'calls'     => array(
                array('setA', array(1)),
            ),
            'properties'     => array(
                array('p1', array(1)),
            ),
            'preTriggers' => array(
                array('service' => '@trigger1', 'method' => 'beforeCreate1', 'arguments' => array('value1'))
            ),
            'postTriggers' => array(
                array('service' => '@trigger1', 'method' => 'afterCreate1', 'arguments' => array('value1'))
            ),
            'alias'     => 'alias1',
            'tags'      => 'tag1',
        ));
        $this->collector->visit('service2', array(
            'parent' => 'service1',
            'class'  => 'B',
            'calls'     => array(
                array('setB', array(2)),
            ),
            'properties'     => array(
                array('p2', array(2)),
            ),
            'preTriggers' => array(
                array('service' => '@trigger2', 'method' => 'beforeCreate2', 'arguments' => array('value2'))
            ),
            'postTriggers' => array(
                array('service' => '@trigger2', 'method' => 'afterCreate2', 'arguments' => array('value2'))
            ),
        ));
        $this->collector->visit('service3', array('class' => 'C'));

        $expectedConfiguration = array(
            'service1' => array(
                'class'     => 'A',
                'arguments' => array(1, 2),
                'calls'     => array(
                    array('setA', array(1)),
                ),
                'properties'     => array(
                    array('p1', array(1)),
                ),
                'preTriggers' => array(
                    array('service' => '@trigger1', 'method' => 'beforeCreate1', 'arguments' => array('value1')),
                ),
                'postTriggers' => array(
                    array('service' => '@trigger1', 'method' => 'afterCreate1', 'arguments' => array('value1')),
                ),
                'alias'     => 'alias1',
                'tags'      => 'tag1',
            ),
            'service3' => array(
                'class' => 'C',
            ),
            'service2' => array(
                'class'     => 'B',
                'arguments' => array(1, 2),
                'calls'     => array(
                    array('setA', array(1)),
                    array('setB', array(2)),
                ),
                'properties'     => array(
                    array('p1', array(1)),
                    array('p2', array(2)),
                ),
                'preTriggers' => array(
                    array('service' => '@trigger1', 'method' => 'beforeCreate1', 'arguments' => array('value1')),
                    array('service' => '@trigger2', 'method' => 'beforeCreate2', 'arguments' => array('value2')),
                ),
                'postTriggers' => array(
                    array('service' => '@trigger1', 'method' => 'afterCreate1', 'arguments' => array('value1')),
                    array('service' => '@trigger2', 'method' => 'afterCreate2', 'arguments' => array('value2')),
                ),
            ),
        );

        $this->assertEquals($expectedConfiguration, $this->collector->getConfiguration());
    }

    /**
     * @expectedException \Butterfly\Component\DI\Builder\ServiceVisitor\InvalidConfigurationException
     */
    public function testVisitIfUndefinedParentService()
    {
        $this->collector->visit('service2', array(
            'parent' => 'service1',
            'class'  => 'B',
        ));

        $this->collector->getConfiguration();
    }
}
