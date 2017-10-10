<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 18/09/2017
 * Time: 12:26
 */

namespace Ftp\MvcAuth\OAuth2\Storage;

use Doctrine\ORM\EntityRepository;
use Ftp\MvcAuth\Entity\OAuthAccessToken;
use Ftp\MvcAuth\Entity\OAuthClient;
use Ftp\MvcAuth\Entity\OAuthEmailRecipient;
use OAuth2\Storage\AccessTokenInterface;

class OAuthAccessTokenStorage extends EntityRepository implements AccessTokenInterface
{
    public function getAccessToken($oauthToken)
    {
        $token = $this->findOneBy(['token' => $oauthToken]);
        if ($token) {
            $token = $token->toArray();
            $token['expires'] = $token['expires']->getTimestamp();
        }
        return $token;
    }

    public function setAccessToken($oauthToken, $clientIdentifier, $userId, $expires, $scope = null)
    {
        $client = $this->_em->getRepository(OAuthClient::class)
            ->findOneBy(['client_identifier' => $clientIdentifier]);
        $user = $this->_em->getRepository(OAuthEmailRecipient::class)
            ->find($userId);
        $token = OAuthAccessToken::fromArray([
            'token'     => $oauthToken,
            'client'    => $client,
            'user'      => $user,
            'expires'   => (new \DateTime())->setTimestamp($expires),
            'scope'     => $scope,
        ]);
        $this->_em->persist($token);
        $this->_em->flush();
    }
}
