<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 09/12/2016
 * Time: 14:47
 */

namespace Ftp\MvcAuth;

use Ftp\MvcAuth\Authentication\OpenIdProxyAdapter;
use Zend\Mvc\MvcEvent;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e)
    {
        $container = $e->getApplication()->getServiceManager();
        /** @var \ZF\MvcAuth\Authentication\DefaultAuthenticationListener $authenticationListener */
        $authenticationListener = $container->get('ZF\MvcAuth\Authentication\DefaultAuthenticationListener');
        $authenticationListener->attach($container->get(OpenIdProxyAdapter::class));
    }
}
