<?php

namespace Ftp\MvcAuth;

use Ftp\MvcAuth\Authentication\OpenIdProxyAdapter;
use Ftp\MvcAuth\Authentication\OpenIdProxyAdapterFactory;
use Ftp\MvcAuth\Authentication\RecipientAdapter;
use Ftp\MvcAuth\Authentication\RecipientAdapterFactory;
use Ftp\MvcAuth\OAuth2\Service\OAuth2ServerFactory;

return [
    'doctrine' => [
        'driver' => [
            'auth_driver' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [
                    0 => __DIR__ . '/../src',
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    'Ftp\MvcAuth' => 'auth_driver',
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            OpenIdProxyAdapter::class => OpenIdProxyAdapterFactory::class,
            RecipientAdapter::class   => RecipientAdapterFactory::class,
            'Ftp\OAuth2\Service\OAuth2Server' => OAuth2ServerFactory::class
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\ProxyAuthController::class => Controller\ProxyAuthControllerFactory::class
        ]
    ],
    'ftp-openid' => [
        'client_secret' => getenv('OPENID_CLIENT_SECRET'),
        'token_endpoint' => getenv('OPENID_TOKEN_ENDPOINT'),
        'userinfo_endpoint' => getenv('OPENID_USERINFO_ENDPOINT'),
        'revocation_endpoint' => getenv('OPENID_REVOCATION_ENDPOINT'),
    ],
    'caches' => [
        'auth.openid.cache' => [
            'adapter' => [
                'name' => 'redis',
                'lifetime' => 300,
                'options' => [
                    'server' => [getenv('REDIS_HOST'), getenv('REDIS_PORT')],
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
    ],
    'router' => [
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
        ],
    ],
];
