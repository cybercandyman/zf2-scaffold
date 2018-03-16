<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace Scaffold\Builder\Service;

use Scaffold\Builder\AbstractBuilder;
use Scaffold\Config;
use Scaffold\Model;
use Scaffold\State;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

class ServiceFactoryBuilder extends AbstractBuilder
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Model
     */
    protected $model;

    /**
     * Prepare models
     *
     * @param State|\Scaffold\State $state
     */
    public function prepare(State $state)
    {
        $model = new Model();
        $name = $this->buildNamespace()
            ->addPart($this->config->getModule())
            ->addPart('Service')
            ->addPart($this->config->getName() . 'ServiceFactory')
            ->getNamespace();

        $path = $this->buildPath()
            ->setModule($this->config->getModule())
            ->addPart('Service')
            ->addPart($this->config->getName() . 'ServiceFactory')
            ->getSourcePath();

        $model->setName($name);
        $model->setPath($path);

        $config = array(
            'service_manager' => array(
                'factories' => array(
                    $model->getServiceName() => $name
                )
            )
        );
        $model->setServiceConfig($config);
        $state->addModel($model, 'service-factory');

        $this->model = $model;
    }

    /**
     * Build generators
     *
     * @param  State|\Scaffold\State $state
     * @return \Scaffold\State|void
     */
    public function build(State $state)
    {
        $model = $this->model;

        $generator = new ClassGenerator($model->getName());
        $generator->setImplementedInterfaces(['Zend\ServiceManager\Factory\FactoryInterface']);
        $generator->addUse('Zend\ServiceManager\Factory\FactoryInterface');
        $generator->addUse('Zend\ServiceManager\ServiceLocatorInterface');
        $generator->addUse('Zend\ServiceManager\ServiceManager');
        $generator->addUse($state->getServiceModel()->getName());

        $method = new MethodGenerator('__invoke');
        $method->setDocBlock(new DocBlockGenerator());
        $method->getDocBlock()->setShortDescription('{@inheritdoc}');
        $method->setParameter(new ParameterGenerator('container', '\Interop\Container\ContainerInterface'));
        $method->setParameter(new ParameterGenerator('requestedName'));
        $options_parameter = new ParameterGenerator('options','array');
        $options_parameter->setDefaultValue(NULL);
        $method->setParameter($options_parameter);
        $method->setBody('return new ' . $state->getServiceModel()->getClassName() . '($container);');
        $generator->addMethodFromGenerator($method);

        $model->setGenerator($generator);
    }

}
