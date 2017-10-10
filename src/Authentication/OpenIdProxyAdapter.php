<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 21/12/2016
 * Time: 14:48
 */

namespace Ftp\MvcAuth\Authentication;

use Zend\Http\Client;
use ZF\MvcAuth\Authentication\AbstractAdapter;
use ZF\MvcAuth\Authentication\AdapterInterface;
use Zend\Http\Request;
use Zend\Http\Response;
use ZF\MvcAuth\Identity;
use ZF\MvcAuth\MvcAuthEvent;

class OpenIdProxyAdapter extends AbstractAdapter
{

    /** @var AdapterInterface */
    protected $upstreamAdapter;

    /**
     * Authorization header token types this adapter can fulfill.
     *
     * @var array
     */
    protected $authorizationTokenTypes = ['bearer'];

    /**
     * Authentication types this adapter provides.
     *
     * @var array
     */
    private $providesTypes = ['openid'];

    /**
     * Request methods that will not have request bodies
     *
     * @var array
     */
    private $requestsWithoutBodies = [
        'GET',
        'HEAD',
        'OPTIONS',
    ];

    /** @var \Zend\Cache\Storage\StorageInterface */
    protected $cache;

    protected $userinfoEndpoint;

    public function __construct($config)
    {
        if ($config['userinfo_endpoint']) {
            $this->userinfoEndpoint = $config['userinfo_endpoint'];
        }
    }

    /**
     * @param \Zend\Cache\Storage\StorageInterface  $cache
     * @return OpenIdProxyAdapter
     */
    public function setCache(\Zend\Cache\Storage\StorageInterface $cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * @return AdapterInterface
     */
    public function getUpstreamAdapter(): ?AdapterInterface
    {
        return $this->upstreamAdapter;
    }

    /**
     * @param AdapterInterface $upstreamAdapter
     * @return OpenIdProxyAdapter
     */
    public function setUpstreamAdapter(AdapterInterface $upstreamAdapter): OpenIdProxyAdapter
    {
        $this->upstreamAdapter = $upstreamAdapter;
        return $this;
    }

    /**
     * @return array Array of types this adapter can handle.
     */
    public function provides()
    {
        return $this->providesTypes;
    }

    /**
     * Attempt to match a requested authentication type
     * against what the adapter provides.
     *
     * @param string $type
     * @return bool
     */
    public function matches($type)
    {
        return in_array($type, $this->providesTypes, true);
    }

    /**
     * Determine if the given request is a type (oauth2) that we recognize
     *
     * @param Request $request
     * @return false|string
     */
    public function getTypeFromRequest(Request $request)
    {
        $type = parent::getTypeFromRequest($request);

        if (false !== $type) {
            return 'openid';
        }

        if (! in_array($request->getMethod(), $this->requestsWithoutBodies)
            && $request->getHeaders()->has('Content-Type')
            && $request->getHeaders()->get('Content-Type')->match('application/x-www-form-urlencoded')
            && $request->getPost('access_token')
        ) {
            return 'openid';
        }

        if (null !== $request->getQuery('access_token')) {
            return 'openid';
        }

        return false;
    }

    /**
     * Perform pre-flight authentication operations.
     *
     * Performs a no-op; nothing needs to happen for this adapter.
     *
     * @param Request $request
     * @param Response $response
     */
    public function preAuth(Request $request, Response $response)
    {
    }

    /**
     * Attempt to authenticate the current request.
     *
     * @param Request $request
     * @param Response $response
     * @param MvcAuthEvent $mvcAuthEvent
     * @return false|Identity\IdentityInterface False on failure, IdentityInterface
     *     otherwise
     */
    public function authenticate(Request $request, Response $response, MvcAuthEvent $mvcAuthEvent)
    {
        if ($this->upstreamAdapter) {
            $identity = $this->upstreamAdapter->authenticate($request, $response, $mvcAuthEvent);
            if ($identity && $identity instanceof Identity\IdentityInterface) {
                return $identity;
            }
        }

        $authorization = $request->getHeader('Authorization');

        if ($authorization) {
            $response = $this->getUserInfo($request);
            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                return $response;
            }
            $data = json_decode($response->getBody(), true);
            $identity = new Identity\AuthenticatedIdentity($data);
            $identity->setName($data['sub']);
            return $identity;
        }

        // Otherwise, no credentials were present at all, so we just return a guest identity.
        return new Identity\GuestIdentity();
    }

    public function getUserInfo(Request $request)
    {
        $headers = $request->getHeaders();
        $authorization = $headers->get('Authorization');
        $cacheKey = $authorization->getFieldValue();

        if (! $this->cache->hasItem($cacheKey)) {
            $client = new Client(null, [
                'adapter' => 'Zend\Http\Client\Adapter\Curl'
            ]);
            $client->setHeaders([$authorization]);
            $client->setUri($this->userinfoEndpoint);
            $response = $client->send();
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                $this->cache->setItem($cacheKey, $response);
            }
        } else {
            $response = $this->cache->getItem($cacheKey);
        }

        return $response;
    }
}
