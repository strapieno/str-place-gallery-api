<?php
namespace Strapieno\PlaceGallery\Api;

use Zend\ModuleManager\Feature\HydratorProviderInterface;
use Zend\ModuleManager\Feature\InputFilterProviderInterface;
use Zend\Stdlib\ArrayUtils;


/**
 * Class Module
 */
class Module implements HydratorProviderInterface
{
    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getHydratorConfig()
    {
        return include __DIR__ . '/config/hydrator.config.php';
    }
}
