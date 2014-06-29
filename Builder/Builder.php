<?php

namespace Syringe\Component\DI\Builder;

use Syringe\Component\DI\Builder\ParameterResolver\IConfigurationResolver;
use Syringe\Component\DI\Builder\ParameterResolver\IConfigurationResolverAware;
use Syringe\Component\DI\Builder\ServiceVisitor\InvalidConfigurationException;
use Syringe\Component\DI\Builder\ServiceVisitor\IVisitor;
use Syringe\Component\DI\Builder\ServiceCollector\IConfigurationCollector;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class Builder implements IConfigurationResolverAware
{
    const SECTION_SERVICES   = 'services';
    const SECTION_PARAMETERS = 'parameters';

    /**
     * @var IConfigurationResolver
     */
    protected $resolver = null;

    /**
     * @var IVisitor[]
     */
    protected $visitors = array();

    /**
     * @var array
     */
    protected $configuration = array();

    /**
     * @param IConfigurationResolver $resolver
     */
    public function setResolver(IConfigurationResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param array $visitors
     */
    public function addServiceVisitors(array $visitors)
    {
        foreach ($visitors as $visitor) {
            $this->addServiceVisitor($visitor);
        }
    }

    /**
     * @param IVisitor $visitor
     */
    public function addServiceVisitor(IVisitor $visitor)
    {
        $this->visitors[] = $visitor;
    }

    /**
     * @param array $configuration
     * @return $this
     */
    public function addConfiguration(array $configuration)
    {
        $this->configuration = array_replace_recursive($this->configuration, $configuration);

        return $this;
    }

    /**
     * @return array
     * @throws InvalidConfigurationException if IoCC configuration is invalid
     */
    public function build()
    {
        $configuration = $this->prepareConfiguration($this->configuration);

        $this->cleanVisitors($this->visitors);
        $this->runServiceVisits($this->visitors, $configuration);
        return array_merge($configuration, $this->resolveServiceConfiguration($this->visitors));
    }

    /**
     * @param IVisitor[] $visitors
     */
    protected function cleanVisitors(array $visitors)
    {
        foreach ($visitors as $visitor) {
            $visitor->clean();
        }
    }

    /**
     * @param array $configuration
     * @return array
     */
    protected function prepareConfiguration(array $configuration)
    {
        if (null !== $this->resolver) {
            $configuration = $this->resolver->resolve($configuration);
        }

        $services = $configuration[self::SECTION_SERVICES];
        unset($configuration[self::SECTION_SERVICES]);

        return array(
            self::SECTION_PARAMETERS => $configuration,
            self::SECTION_SERVICES   => $services,
        );
    }

    /**
     * @param IVisitor[] $visitors
     * @param array $configuration
     * @throws InvalidConfigurationException if IoCC configuration is invalid
     */
    protected function runServiceVisits(array $visitors, array $configuration)
    {
        $errors = array();

        foreach ($configuration[self::SECTION_SERVICES] as $serviceId => $serviceConfiguration) {
            foreach ($visitors as $visitor) {
                try {
                    $visitor->visit($serviceId, $serviceConfiguration);
                } catch (InvalidConfigurationException $e) {
                    $errors[] = $e;
                }
            }
        }

        if (!empty($errors)) {
            throw new InvalidConfigurationException('IoCC configuration is invalid', $errors);
        }
    }

    /**
     * @param IVisitor[] $visitors
     * @return array
     */
    protected function resolveServiceConfiguration(array $visitors)
    {
        $containerConfiguration = array();

        foreach ($visitors as $visitor) {
            if (!($visitor instanceof IConfigurationCollector)) {
                continue;
            }

            $containerConfiguration[$visitor->getSection()] = $visitor->getConfiguration();
        }

        return $containerConfiguration;
    }
}
