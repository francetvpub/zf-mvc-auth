<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 21/12/2016
 * Time: 15:43
 */

namespace Ftp\MvcAuth\Controller;

use Ftp\MvcAuth\Authentication\OpenIdProxyAdapter;
use OAuth2\Request as OAuth2Request;
use OAuth2\Response as OAuth2Response;
use OAuth2\Server as OAuth2Server;
use Zend\Http\Client;
use Zend\Http\PhpEnvironment\Request as PhpEnvironmentRequest;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
use ZF\ApiProblem\Exception\ProblemExceptionInterface;

class ProxyAuthController extends AbstractActionController
{
    /**
     * @var boolean
     */
    protected $apiProblemErrorResponse = true;

    protected $tokenEndpoint;

    protected $userinfoEndpoint;

    protected $clientSecret;

    /**
     * @var OAuth2Server
     */
    protected $oauth2Server;

    /**
     * @var OpenIdProxyAdapter
     */
    protected $openIdAdapter;

    public function __construct($config)
    {
        if ($config['token_endpoint']) {
            $this->tokenEndpoint = $config['token_endpoint'];
        }
        if ($config['userinfo_endpoint']) {
            $this->userinfoEndpoint = $config['userinfo_endpoint'];
        }
        if ($config['revocation_endpoint']) {
            $this->revocationEndpoint = $config['revocation_endpoint'];
        }
        if ($config['client_secret']) {
            $this->clientSecret = $config['client_secret'];
        }
    }

    /**
     * @param OpenIdProxyAdapter $openIdAdapter
     * @return ProxyAuthController
     */
    public function setOpenIdAdapter(OpenIdProxyAdapter $openIdAdapter): ProxyAuthController
    {
        $this->openIdAdapter = $openIdAdapter;
        return $this;
    }

    /**
     * @param OAuth2Server $oauth2Server
     * @return ProxyAuthController
     */
    public function setOauth2Server(OAuth2Server $oauth2Server): ProxyAuthController
    {
        $this->oauth2Server = $oauth2Server;
        return $this;
    }

    /**
     * Retrieve the OAuth2\Server instance.
     *
     * @return OAuth2Server
     */
    protected function getOAuth2Server()
    {
        return $this->oauth2Server;
    }

    /**
     * Should the controller return ApiProblemResponse?
     *
     * @return bool
     */
    public function isApiProblemErrorResponse()
    {
        return $this->apiProblemErrorResponse;
    }

    /**
     * Indicate whether ApiProblemResponse or oauth2 errors should be returned.
     *
     * Boolean true indicates ApiProblemResponse should be returned (the
     * default), while false indicates oauth2 errors (per the oauth2 spec)
     * should be returned.
     *
     * @param bool $apiProblemErrorResponse
     */
    public function setApiProblemErrorResponse($apiProblemErrorResponse)
    {
        $this->apiProblemErrorResponse = (bool) $apiProblemErrorResponse;
    }

    public function internalTokenAction()
    {
        $request = $this->getRequest();
        if (! $request instanceof HttpRequest) {
            // not an HTTP request; nothing left to do
            return;
        }

        if ($request->isOptions()) {
            // OPTIONS request.
            // This is most likely a CORS attempt; as such, pass the response on.
            return $this->getResponse();
        }

        $oauth2request = $this->getOAuth2Request();
        $oauth2server = $this->getOAuth2Server();
        try {
            $response = $oauth2server->handleTokenRequest($oauth2request);
        } catch (ProblemExceptionInterface $ex) {
            return new ApiProblemResponse(
                new ApiProblem(401, $ex)
            );
        }

        if ($response->isClientError()) {
            return $this->getErrorResponse($response);
        }

        return $this->setHttpResponse($response);
    }

    public function tokenAction()
    {
        $response = $this->internalTokenAction();

        if ($response instanceof \Zend\Http\Response && $response->getStatusCode() == 200) {
            return $response;
        }

        /** @var \ZF\ContentNegotiation\Request $request */
        $request = $this->getRequest();
        $headers = $request->getHeaders();
        $headers->clearHeaders();
        $request->setUri($this->tokenEndpoint);
        $content = $request->getContent();
        $content = $content."&client_secret=".$this->clientSecret;
        $request->setContent($content);
        $client = new Client(null, [
            'adapter' => 'Zend\Http\Client\Adapter\Curl'
        ]);
        return $client->send($request);
    }

    public function userinfoAction()
    {
        /** @var \ZF\ContentNegotiation\Request $request */
        $request = $this->getRequest();
        return $this->openIdAdapter->getUserInfo($request);
    }

    /**
     * @param OAuth2Response $response
     * @return ApiProblemResponse|\Zend\Stdlib\ResponseInterface
     */
    protected function getErrorResponse(OAuth2Response $response)
    {
        if ($this->isApiProblemErrorResponse()) {
            return $this->getApiProblemResponse($response);
        }

        return $this->setHttpResponse($response);
    }

    /**
     * Map OAuth2Response to ApiProblemResponse
     *
     * @param OAuth2Response $response
     * @return ApiProblemResponse
     */
    protected function getApiProblemResponse(OAuth2Response $response)
    {
        $parameters       = $response->getParameters();
        $errorUri         = isset($parameters['error_uri']) ? $parameters['error_uri'] : null;
        $error            = isset($parameters['error']) ? $parameters['error'] : null;
        $errorDescription = isset($parameters['error_description']) ? $parameters['error_description'] : null;

        return new ApiProblemResponse(
            new ApiProblem(
                $response->getStatusCode(),
                $errorDescription,
                $errorUri,
                $error
            )
        );
    }

    /**
     * Convert the OAuth2 response to a \Zend\Http\Response
     *
     * @param $response OAuth2Response
     * @return \Zend\Http\Response
     */
    private function setHttpResponse(OAuth2Response $response)
    {
        $httpResponse = $this->getResponse();
        $httpResponse->setStatusCode($response->getStatusCode());

        $headers = $httpResponse->getHeaders();
        $headers->addHeaders($response->getHttpHeaders());
        $headers->addHeaderLine('Content-type', 'application/json');

        $httpResponse->setContent($response->getResponseBody());
        return $httpResponse;
    }

    /**
     * Create an OAuth2 request based on the ZF2 request object
     *
     * Marshals:
     *
     * - query string
     * - body parameters, via content negotiation
     * - "server", specifically the request method and content type
     * - raw content
     * - headers
     *
     * This ensures that JSON requests providing credentials for OAuth2
     * verification/validation can be processed.
     *
     * @return OAuth2Request
     */
    protected function getOAuth2Request()
    {
        $zf2Request = $this->getRequest();
        $headers    = $zf2Request->getHeaders();

        // Marshal content type, so we can seed it into the $_SERVER array
        $contentType = '';
        if ($headers->has('Content-Type')) {
            $contentType = $headers->get('Content-Type')->getFieldValue();
        }

        // Get $_SERVER superglobal
        $server = [];
        if ($zf2Request instanceof PhpEnvironmentRequest) {
            $server = $zf2Request->getServer()->toArray();
        } elseif (! empty($_SERVER)) {
            $server = $_SERVER;
        }
        $server['REQUEST_METHOD'] = $zf2Request->getMethod();

        // Seed headers with HTTP auth information
        $headers = $headers->toArray();
        if (isset($server['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $server['PHP_AUTH_USER'];
        }
        if (isset($server['PHP_AUTH_PW'])) {
            $headers['PHP_AUTH_PW'] = $server['PHP_AUTH_PW'];
        }

        // Ensure the bodyParams are passed as an array
        $bodyParams = $this->bodyParams() ?: [];

        return new OAuth2Request(
            $zf2Request->getQuery()->toArray(),
            $bodyParams,
            [], // attributes
            [], // cookies
            [], // files
            $server,
            $zf2Request->getContent(),
            $headers
        );
    }
}
