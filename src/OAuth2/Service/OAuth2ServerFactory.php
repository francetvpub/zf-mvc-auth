<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 18/09/2017
 * Time: 16:24
 */

namespace Ftp\MvcAuth\OAuth2\Service;

use Doctrine\ORM\EntityManager;
use Ftp\MvcAuth\Entity\OAuthAccessToken;
use Ftp\MvcAuth\Entity\OAuthClient;
use Ftp\MvcAuth\Entity\OAuthRefreshToken;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class OAuth2ServerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);

        $server = new \OAuth2\Server([
            'client_credentials' => $entityManager->getRepository(OAuthClient::class),
            'authorization_code' => $entityManager->getRepository(OAuthClient::class),
            'access_token'       => $entityManager->getRepository(OAuthAccessToken::class),
            'refresh_token'      => $entityManager->getRepository(OAuthRefreshToken::class)
        ], [
            'always_issue_new_refresh_token' => true
        ]);

        return $server;
    }
}
