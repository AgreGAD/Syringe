<?php

namespace Butterfly\Component\DI\Tests\Compiler;

use Butterfly\Component\DI\Compiler\ConfigCompiler;
use Butterfly\Component\DI\Compiler\ServiceCollector;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ConfigCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateInstance()
    {
        $compiler = ConfigCompiler::createInstance();

        $this->assertInstanceOf('\Butterfly\Component\DI\Compiler\ConfigCompiler', $compiler);
    }

    public function testCompileConfig()
    {
        $input = array(
            'services'   => array(
                'service.simple' => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, '2'),
                ),
            ),
        );

        $exptected = array(
            'services'   => array(
                'service.simple' => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, '2'),
                ),
            ),
            'parameters'         => array(),
            'tags'               => array(),
            'aliases'            => array(),
            'interfaces'         => array(),
            'interfaces_aliases' => array(),
        );

        $compiler      = ConfigCompiler::createInstance();
        $configuration = $compiler->compileConfig($input);

        $this->assertEquals($exptected, $configuration);
    }

    public function testDoubleCompile()
    {
        $input = array(
            'services'   => array(
                'service.simple' => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, '2'),
                ),
            ),
        );

        $exptected = array(
            'services'   => array(
                'service.simple' => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, '2'),
                ),
            ),
            'parameters'         => array(),
            'tags'               => array(),
            'aliases'            => array(),
            'interfaces'         => array(),
            'interfaces_aliases' => array(),
        );

        ConfigCompiler::compile($input);
        $configuration = ConfigCompiler::compile($input);

        $this->assertEquals($exptected, $configuration);
    }

    public function getTestDataForTestCompile()
    {
        return array(
            // empty input config
            array(
                array(),
                array(
                    'parameters'         => array(),
                    'services'           => array(),
                    'tags'               => array(),
                    'aliases'            => array(),
                    'interfaces'         => array(),
                    'interfaces_aliases' => array(),
                ),
            ),

            // simple parameters
            array(
                array(
                    'parameter_string'  => 'abz',
                    'parameter_string2' => 'b22222',
                    'parameter_array'   => array(1, 2, 3),
                    'parameter_text' => 'abz/b22222',
                ),
                array(
                    'parameters'         => array(
                        'parameter_string'  => 'abz',
                        'parameter_string2' => 'b22222',
                        'parameter_array'   => array(1, 2, 3),
                        'parameter_text' => 'abz/b22222',
                    ),
                    'services'           => array(),
                    'tags'               => array(),
                    'aliases'            => array(),
                    'interfaces'         => array(),
                    'interfaces_aliases' => array(),
                )
            ),

            // replaces in parameters
            array(
                array(
                    'parameter_name'   => 'world',
                    'parameter_result' => 'hello %parameter_name%',
                ),
                array(
                    'parameters'         => array(
                        'parameter_name'   => 'world',
                        'parameter_result' => 'hello world',
                    ),
                    'services'           => array(),
                    'tags'               => array(),
                    'aliases'            => array(),
                    'interfaces'         => array(),
                    'interfaces_aliases' => array(),
                )
            ),

            // replaces in services
            array(
                array(
                    'service.name'  => 'service.injected_parameters',
                    'service.class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'service.argument.a' => 123,
                    'SERVICE.ARGUMENT.B' => 'abc',
                    'service.property.a' => true,

                    'services' => array(
                        '%service.name%'   => array(
                            'class'      => '%service.class%',
                            'arguments'  => array('%service.argument.a%', '%SERVICE.ARGUMENT.B%'),
                            'properties' => array(
                                'a' => '%service.property.a%',
                            ),
                        ),
                    ),
                ),
                array(
                    'parameters'         => array(
                        'service.name'  => 'service.injected_parameters',
                        'service.class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                        'service.argument.a' => 123,
                        'SERVICE.ARGUMENT.B' => 'abc',
                        'service.property.a' => true,
                    ),
                    'services'           => array(
                        'service.injected_parameters'   => array(
                            'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                            'arguments'  => array(123, 'abc'),
                            'properties' => array(
                                'a' => true,
                            ),
                        ),
                    ),
                    'tags'               => array(),
                    'aliases'            => array(),
                    'interfaces'         => array(),
                    'interfaces_aliases' => array(),
                )
            ),

            // service injection
            array(
                array(
                    'services' => array(
                        'service.simple'   => array(
                            'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                            'arguments'  => array(1, '2'),
                        ),
                        'service.with_injections'   => array(
                            'class'      => 'Butterfly\Component\DI\Tests\Stubs\ComplexServiceStub',
                            'arguments'  => array('@service.simple'),
                            'calls' => array(
                                array('setInternalService', array('@service.simple')),
                            ),
                        ),
                    ),
                ),
                array(
                    'parameters' => array(),
                    'services' => array(
                        'service.simple'   => array(
                            'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                            'arguments'  => array(1, '2'),
                        ),
                        'service.with_injections'   => array(
                            'class'      => 'Butterfly\Component\DI\Tests\Stubs\ComplexServiceStub',
                            'arguments'  => array('@service.simple'),
                            'calls' => array(
                                array('setInternalService', array('@service.simple')),
                            ),
                        ),
                    ),
                    'tags'               => array(),
                    'aliases'            => array(),
                    'interfaces'         => array(),
                    'interfaces_aliases' => array(),
                )
            ),

            // service inheritor
            array(
                array(
                    'services' => array(
                        'service.simple'   => array(
                            'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                            'arguments'  => array(1, '2'),
                        ),
                        'service.simple.inheritor'      => array(
                            'class'  => 'Butterfly\Component\DI\Tests\Stubs\ServiceStubInheritor',
                            'parent' => 'service.simple',
                        ),
                    ),
                ),
                array(
                    'parameters' => array(),
                    'services' => array(
                        'service.simple'   => array(
                            'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                            'arguments'  => array(1, '2'),
                        ),
                        'service.simple.inheritor'      => array(
                            'class'  => 'Butterfly\Component\DI\Tests\Stubs\ServiceStubInheritor',
                            'arguments'  => array(1, '2'),
                            'calls' => array(),
                            'properties' => array(),
                            'preTriggers' => array(),
                            'postTriggers' => array(),
                        ),
                    ),
                    'tags'               => array(),
                    'aliases'            => array(),
                    'interfaces'         => array(),
                    'interfaces_aliases' => array(),
                )
            ),

            // tags
            array(
                array(
                    'services' => array(
                        'service.simple.a'   => array(
                            'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                            'arguments'  => array(1, '2'),
                            'tags' => 'tag.a'
                        ),
                        'service.simple.b'      => array(
                            'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                            'arguments'  => array(1, '2'),
                            'tags' => array('tag.a', 'tag.b')
                        ),
                    ),
                ),
                array(
                    'parameters' => array(),
                    'services' => array(
                        'service.simple.a'   => array(
                            'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                            'arguments'  => array(1, '2'),
                            'tags' => 'tag.a'
                        ),
                        'service.simple.b'      => array(
                            'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                            'arguments'  => array(1, '2'),
                            'tags' => array('tag.a', 'tag.b')
                        ),
                    ),
                    'tags'               => array(
                        'tag.a' => array('service.simple.a', 'service.simple.b'),
                        'tag.b' => array('service.simple.b'),
                    ),
                    'aliases'            => array(),
                    'interfaces'         => array(),
                    'interfaces_aliases' => array(),
                )
            ),

            // aliases
            array(
                array(
                    'services' => array(
                        'service.simple.a'   => array(
                            'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                            'arguments'  => array(1, '2'),
                            'alias' => 'service.alias'
                        ),
                    ),
                ),
                array(
                    'parameters' => array(),
                    'services' => array(
                        'service.simple.a'   => array(
                            'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                            'arguments'  => array(1, '2'),
                            'alias' => 'service.alias',
                        ),
                    ),
                    'tags'               => array(),
                    'aliases'            => array(
                        'service.alias' => 'service.simple.a'
                    ),
                    'interfaces'         => array(),
                    'interfaces_aliases' => array(),
                )
            ),

            // interfaces
            array(
                array(
                    'interfaces'        => array(
                        'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware' => 'service.simple',
                        'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware2' => array(
                            'service' => 'service.simple',
                            'alias' => 'foo.aware'
                        ),
                        'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware3' => array(
                            'service' => 'service.simple',
                            'alias' => array(
                                'foo.aware.1',
                                'foo.aware.2',
                            ),
                        ),
                    ),

                    'services' => array(
                        'service.simple.a'   => array(
                            'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                            'arguments'  => array(1, '2'),
                        ),
                    ),
                ),
                array(
                    'parameters' => array(),
                    'services' => array(
                        'service.simple.a'   => array(
                            'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                            'arguments'  => array(1, '2'),
                        ),
                    ),
                    'tags'               => array(),
                    'aliases'            => array(),
                    'interfaces'         => array(
                        'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware' => 'service.simple',
                        'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware2' => 'service.simple',
                        'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware3' => 'service.simple',
                    ),
                    'interfaces_aliases' => array(
                        'foo.aware' => 'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware2',
                        'foo.aware.1' => 'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware3',
                        'foo.aware.2' => 'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware3',
                    ),
                )
            ),
        );
    }

    /**
     * @dataProvider getTestDataForTestCompile
     *
     * @param array $inputConfig
     * @param array $expectedConfig
     */
    public function testCompile(array $inputConfig, array $expectedConfig)
    {
        $config = ConfigCompiler::compile($inputConfig);

        $this->assertEquals($expectedConfig, $config);
    }
}
