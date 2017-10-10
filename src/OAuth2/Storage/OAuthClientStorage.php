<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 18/09/2017
 * Time: 12:11
 */

namespace Ftp\MvcAuth\OAuth2\Storage;

use Doctrine\ORM\EntityRepository;
use Ftp\MvcAuth\Entity\EncryptableFieldEntityTrait;
use Ftp\MvcAuth\Entity\OAuthClient;
use Ftp\MvcAuth\Entity\OAuthEmailRecipient;
use OAuth2\Storage\AuthorizationCodeInterface;
use OAuth2\Storage\ClientCredentialsInterface;

class OAuthClientStorage extends EntityRepository implements ClientCredentialsInterface, AuthorizationCodeInterface
{

    use EncryptableFieldEntityTrait;

    /**
     * @param $clientIdentifier
     * @return null|array
     */
    public function getClientDetails($clientIdentifier) : ?array
    {
        /** @var OAuthClient $client */
        $client = $this->findOneBy(['client_identifier' => $clientIdentifier]);
        if ($client) {
            $client = $client->toArray();
        }
        return $client;
    }

    /**
     * @param $clientIdentifier
     * @param null $clientSecret
     * @return bool
     */
    public function checkClientCredentials($clientIdentifier, $clientSecret = null) : bool
    {
        /** @var OAuthClient $client */
        $client = $this->findOneBy(['client_identifier' => $clientIdentifier]);
        if ($client) {
            return $client->verifyClientSecret($clientSecret);
        }
        return false;
    }

    public function checkRestrictedGrantType($clientId, $grantType) : bool
    {
        // we do not support different grant types per client in this implementation
        return true;
    }

    public function isPublicClient($clientId) : bool
    {
        return false;
    }

    public function getClientScope($clientId) : void
    {
        return;
    }

    /**
     * Fetch authorization code data (probably the most common grant type).
     *
     * Retrieve the stored data for the given authorization code.
     *
     * Required for OAuth2::GRANT_TYPE_AUTH_CODE.
     *
     * @param $code
     * Authorization code to be check with.
     *
     * @return
     * An associative array as below, and NULL if the code is invalid
     * @code
     * return array(
     *     "client_id"    => CLIENT_ID,      // REQUIRED Stored client identifier
     *     "user_id"      => USER_ID,        // REQUIRED Stored user identifier
     *     "expires"      => EXPIRES,        // REQUIRED Stored expiration in unix timestamp
     *     "redirect_uri" => REDIRECT_URI,   // REQUIRED Stored redirect URI
     *     "scope"        => SCOPE,          // OPTIONAL Stored scope values in space-separated string
     * );
     * @endcode
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.1
     *
     * @ingroup oauth2_section_4
     */
    public function getAuthorizationCode($code)
    {
        /** @var OAuthEmailRecipient $user */
        $user = $this->_em->getRepository(OAuthEmailRecipient::class)
            ->find($code);
        /** @var OAuthClient $client */
        $client = array_pop($this->findAll());
        if ($user) {
            return [
                'client_id' => $client->getClientIdentifier(),
                'user_id' => $user->getId(),
                'expires' => time() + 60,
                'redirect_uri' => '',
                'scope' => ''
            ];
        }
    }

    /**
     * Take the provided authorization code values and store them somewhere.
     *
     * This function should be the storage counterpart to getAuthCode().
     *
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * Required for OAuth2::GRANT_TYPE_AUTH_CODE.
     *
     * @param string $code Authorization code to be stored.
     * @param mixed $client_id Client identifier to be stored.
     * @param mixed $user_id User identifier to be stored.
     * @param string $redirect_uri Redirect URI(s) to be stored in a space-separated string.
     * @param int $expires Expiration to be stored as a Unix timestamp.
     * @param string $scope OPTIONAL Scopes to be stored in space-separated string.
     *
     * @ingroup oauth2_section_4
     */
    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null)
    {
        // we actually do not support authorization code storage right now
    }

    /**
     * once an Authorization Code is used, it must be expired
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4.1.2
     *
     *    The client MUST NOT use the authorization code
     *    more than once.  If an authorization code is used more than
     *    once, the authorization server MUST deny the request and SHOULD
     *    revoke (when possible) all tokens previously issued based on
     *    that authorization code
     *
     */
    public function expireAuthorizationCode($code)
    {
        // as authorization code is currently the user id, we do not delete it
    }
}
