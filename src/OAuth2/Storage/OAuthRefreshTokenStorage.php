<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 04/10/2017
 * Time: 12:15
 */

namespace Ftp\MvcAuth\OAuth2\Storage;

use Doctrine\ORM\EntityRepository;
use Ftp\MvcAuth\Entity\OAuthClient;
use Ftp\MvcAuth\Entity\OAuthEmailRecipient;
use Ftp\MvcAuth\Entity\OAuthRefreshToken;
use OAuth2\Storage\RefreshTokenInterface;

class OAuthRefreshTokenStorage extends EntityRepository implements RefreshTokenInterface
{
    /**
     * @param $refreshToken
     * @return null|object
     */
    public function getRefreshToken($refreshToken)
    {
        $refreshToken = $this->findOneBy(['refresh_token' => $refreshToken]);
        if ($refreshToken) {
            $refreshToken = $refreshToken->toArray();
            $refreshToken['expires'] = $refreshToken['expires']->getTimestamp();
        }
        return $refreshToken;
    }

    /**
     * @param $refreshToken
     * @param $clientIdentifier
     * @param $userId
     * @param $expires
     * @param null $scope
     */
    public function setRefreshToken($refreshToken, $clientIdentifier, $userId, $expires, $scope = null)
    {
        $client = $this->_em->getRepository(OAuthClient::class)
            ->findOneBy(['client_identifier' => $clientIdentifier]);
        $user = $this->_em->getRepository(OAuthEmailRecipient::class)
            ->find($userId);
        $refreshToken = OAuthRefreshToken::fromArray([
            'refresh_token'  => $refreshToken,
            'client'         => $client,
            'user'           => $user,
            'expires'        => (new \DateTime())->setTimestamp($expires),
            'scope'          => $scope,
        ]);
        $this->_em->persist($refreshToken);
        $this->_em->flush();
    }

    /**
     * @param $refreshToken
     */
    public function unsetRefreshToken($refreshToken)
    {
        $refreshToken = $this->findOneBy(['refresh_token' => $refreshToken]);
        $this->_em->remove($refreshToken);
        $this->_em->flush();
    }
}
