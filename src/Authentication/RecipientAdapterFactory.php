<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 21/12/2016
 * Time: 18:13
 */

namespace Ftp\MvcAuth\Authentication;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class RecipientAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $server = $container->get('Ftp\OAuth2\Service\OAuth2Server');

        return new RecipientAdapter($server);
    }
}
