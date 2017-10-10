<?php

namespace Ftp\MvcAuth\Authentication;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class OpenIdProxyAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $cache = $container->get('auth.openid.cache');

        $adapter = new OpenIdProxyAdapter($container->get('config')['ftp-openid']);
        $adapter->setCache($cache);

        $upstreamAdapter = $container->get(RecipientAdapter::class);
        if ($upstreamAdapter) {
            $adapter->setUpstreamAdapter($upstreamAdapter);
        }

        return $adapter;
    }
}
