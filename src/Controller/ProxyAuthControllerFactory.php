<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 21/12/2016
 * Time: 15:43
 */

namespace Ftp\MvcAuth\Controller;

use Ftp\MvcAuth\Authentication\OpenIdProxyAdapter;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ProxyAuthControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ProxyAuthController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $authController = new ProxyAuthController($container->get('config')['ftp-openid']);

        $authController->setApiProblemErrorResponse(
            $this->marshalApiProblemErrorResponse($container)
        );

        $authController->setOpenIdAdapter($container->get(OpenIdProxyAdapter::class));
        $authController->setOauth2Server($container->get('Ftp\OAuth2\Service\OAuth2Server'));

        return $authController;
    }

    /**
     * Determine whether or not to render API Problem error responses.
     *
     * @param ContainerInterface $container
     * @return bool
     */
    private function marshalApiProblemErrorResponse(ContainerInterface $container)
    {
        if (! $container->has('config')) {
            return false;
        }

        $config = $container->get('config');

        return (isset($config['zf-oauth2']['api_problem_error_response'])
            && $config['zf-oauth2']['api_problem_error_response'] === true);
    }
}
