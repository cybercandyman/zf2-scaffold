<?php
/**
 * @author Evgeny Shpilevsky <evgeny@shpilevsky.com>
 */

namespace Scaffold\Builder\Service;

use Scaffold\Builder\AbstractBuilder;
use Scaffold\Code\Generator\TraitGenerator;
use Scaffold\Config;
use Scaffold\Model;
use Scaffold\State;
use Zend\Code\Generator\DocBlock\Tag;

class ServiceTraitBuilder extends AbstractBuilder
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
            ->addPart($this->config->getName() . 'ServiceTrait')
            ->getNamespace();

        $path = $this->buildPath()
            ->setModule($this->config->getModule())
            ->addPart('Service')
            ->addPart($this->config->getName() . 'ServiceTrait')
            ->getSourcePath();

        $model->setName($name);
        $model->setPath($path);
        $state->addModel($model, 'service-trait');

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

        $generator = new TraitGenerator($model->getName());
        $generator->addUse($state->getServiceModel()->getName());
        $generator->addUse($state->getModel('RuntimeException')->getName());
        $generator->addUse('Zend\ServiceManager\ServiceLocatorInterface');
        $generator->addUse('Interop\Container\ContainerInterface');
        $property = lcfirst($state->getServiceModel()->getClassName());
        $class = $state->getServiceModel()->getClassName();
        $alias = $state->getServiceModel()->getServiceName();

        $code
            = <<<EOF
if (null === \$this->$property) {
    if (method_exists(\$this, 'getServiceManager') &&   \$this->getServiceManager()  instanceof ContainerInterface) {
        if( \$this->getServiceManager()->has('$alias') ) {
            \$this->$property = \$this->getServiceManager()->get('$alias');
        }else{
            throw new RuntimeException('Service ApplicationUserService not found');
        }        
    } else if (property_exists(\$this, 'serviceManager')
            && \$this->serviceManager instanceof ContainerInterface
        ) 
    {
            \$this->$property = \$this->serviceManager->get('$alias');
    } else {
            throw new RuntimeException('Service manager not found');
    }
    
}
return \$this->$property;
EOF;

        $this->addProperty($generator, $property, $class);
        $this->addSetter($generator, $property, $state->getServiceModel()->getName());

        $getter = $this->getGetter($property, $class);
        $getter->setBody($code);
        $getter->getDocBlock()->setTag(
            new Tag\GenericTag('throws', $state->getModel('RuntimeException')->getClassName())
        );
        $generator->addMethodFromGenerator($getter);
//
        $model->setGenerator($generator);
    }
}

/**
 *

public function getUserService()
{
    if (null === $this->userService) {
        if (method_exists($this, 'getServiceManager') &&  $this->getServiceManager()  instanceof ContainerInterface) {
            if( $this->getServiceManager()->has("ApplicationUserService") ){
                $this->userService = $this->getServiceManager()->get('ApplicationUserService');
            }else{
                throw new RuntimeException('Service ApplicationUserService not found');
            }
        }
        else if (property_exists($this, 'serviceManager')
            && $this->serviceManager instanceof ContainerInterface
        ) {
            $this->userService = $this->serviceManager->get('ApplicationUserService');
        }
        else {
            throw new RuntimeException('Service manager not found');
        }
    }

    return $this->userService;
}

*/