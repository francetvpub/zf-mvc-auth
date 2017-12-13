<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zend-log for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Ftp\MvcAuth;

use Ftp\MvcAuth\Authentication\OpenIdProxyAdapter;
use Ftp\MvcAuth\Authentication\OpenIdProxyAdapterFactory;
use Ftp\MvcAuth\Authentication\RecipientAdapter;
use Ftp\MvcAuth\Authentication\RecipientAdapterFactory;
use Ftp\MvcAuth\OAuth2\Service\OAuth2ServerFactory;

class ConfigProvider
{
    /**
     * Return configuration for this component.
     *
     * @return array
     */
    public function __invoke() : array
    {
        return [
            'doctrine' => $this->getDoctrineConfig(),
            'controllers' => $this->getControllersConfig(),
            'service_manager' => $this->getDependencyConfig(),
            'ftp-openid' => $this->getOpenIdConfig(),
            'caches' => $this->getCachesConfig(),
            'router' => $this->getRouterConfig()
        ];
    }

    public function getOpenIdConfig() : array
    {
        return [
            'client_secret' => (string)getenv('OPENID_CLIENT_SECRET'),
            'token_endpoint' => (string)getenv('OPENID_TOKEN_ENDPOINT'),
            'userinfo_endpoint' => (string)getenv('OPENID_USERINFO_ENDPOINT'),
            'revocation_endpoint' => (string)getenv('OPENID_REVOCATION_ENDPOINT')
        ];
    }

    public function getCachesConfig() : array
    {
        return [
            'auth.openid.cache' => [
                'adapter' => [
                    'name' => 'redis',
                    'lifetime' => 300,
                    'options' => [
                        'server' => [ getenv('REDIS_HOST'), getenv('REDIS_PORT') ],
                        'namespace'  => 'openid',
                        'lib_options' => [
                            \Redis::OPT_SERIALIZER => \Redis::SERIALIZER_PHP
                        ]
                    ]
                ],
                'plugins' => [
                    'exception_handler' => [
                        'throw_exceptions' => false
                    ]
                ]
            ]
        ];
    }

    public function getRouterConfig() : array
    {
        return [
            'routes' => [
                'openid' => [
                    'type' => 'literal',
                    'options' => [
                        'route'    => '/oauth',
                        'defaults' => [
                            'controller' => Controller\ProxyAuthController::class,
                            'action'     => 'token',
                        ],
                    ],
                    'may_terminate' => true,
                    'child_routes' => [
                        'token' => [
                            'type' => 'literal',
                            'options' => [
                                'route' => '/token',
                            ],
                        ],
                        'userinfo' => [
                            'type' => 'literal',
                            'options' => [
                                'route' => '/userinfo',
                                'defaults' => [
                                    'action' => 'userinfo',
                                ],
                            ],
                        ],
                        'revoke' => [
                            'type' => 'literal',
                            'options' => [
                                'route' => '/revoke',
                                'defaults' => [
                                    'action' => 'revoke',
                                ],
                            ],
                        ],
                        'authorize' => [
                            'type' => 'literal',
                            'options' => [
                                'route' => '/authorize',
                                'defaults' => [
                                    'action' => 'authorize',
                                ],
                            ],
                        ],
                        'resource' => [
                            'type' => 'literal',
                            'options' => [
                                'route' => '/resource',
                                'defaults' => [
                                    'action' => 'resource',
                                ],
                            ],
                        ],
                        'code' => [
                            'type' => 'literal',
                            'options' => [
                                'route' => '/receivecode',
                                'defaults' => [
                                    'action' => 'receiveCode',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];
    }

    public function getControllersConfig() : array
    {
        return [
            'factories' => [
                Controller\ProxyAuthController::class => Controller\ProxyAuthControllerFactory::class
            ]
        ];
    }

    /**
     * Return dependency mappings for this component.
     *
     * @return array
     */
    public function getDependencyConfig() : array
    {
        return [
            'factories' => [
                OpenIdProxyAdapter::class => OpenIdProxyAdapterFactory::class,
                RecipientAdapter::class   => RecipientAdapterFactory::class,
                'Ftp\OAuth2\Service\OAuth2Server' => OAuth2ServerFactory::class
            ]
        ];
    }

    public function getDoctrineConfig() : array
    {
        return [
            'driver' => [
                'auth_driver' => [
                    'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                    'cache' => 'array',
                    'paths' => [ __DIR__ ]
                ],
                'orm_default' => [
                    'drivers' => [
                        'Ftp\MvcAuth' => 'auth_driver',
                    ]
                ]
            ]
        ];
    }
}
