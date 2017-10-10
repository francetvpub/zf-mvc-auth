<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 04/10/2017
 * Time: 12:02
 */

namespace Ftp\MvcAuth\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class OAuthRefreshToken
 * @ORM\Entity(repositoryClass="Ftp\MvcAuth\OAuth2\Storage\OAuthRefreshTokenStorage")
 * @ORM\Table(name="OAUTH_REFRESH_TOKEN")
 */
class OAuthRefreshToken
{

    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\SequenceGenerator(sequenceName="OAUTH_REFRESH_TOKEN_ID")
     * @ORM\Column(type="integer", name="ID")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=40, unique=true)
     */
    private $refresh_token;

    /**
     * @var string
     * @ORM\Column(type="integer", name="CLIENT_ID")
     */
    private $client_id;

    /**
     * @var string
     * @ORM\Column(type="string", name="USER_ID", nullable=true)
     */
    private $user_id;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $expires;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $scope;

    /**
     * @var OAuthClient
     * @ORM\ManyToOne(targetEntity="Ftp\MvcAuth\Entity\OAuthClient")
     * @ORM\JoinColumn(name="CLIENT_ID", referencedColumnName="ID")
     */
    private $client;

    /**
     * @var OAuthEmailRecipient
     * @ORM\ManyToOne(targetEntity="Ftp\MvcAuth\Entity\OAuthEmailRecipient")
     * @ORM\JoinColumn(name="USER_ID", referencedColumnName="ID")
     */
    private $user;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set refresh_token
     *
     * @param string $refresh_token
     * @return OAuthRefreshToken
     */
    public function setRefreshToken($refresh_token)
    {
        $this->refresh_token = $refresh_token;

        return $this;
    }

    /**
     * Get refresh_token
     *
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    /**
     * Set client_id
     *
     * @param string $clientId
     * @return OAuthRefreshToken
     */
    public function setClientId($clientId)
    {
        $this->client_id = $clientId;

        return $this;
    }

    /**
     * Get client_id
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->getClient()->getClientIdentifier();
    }

    /**
     * Set user_id
     *
     * @param string $userId
     * @return OAuthRefreshToken
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get user_identifier
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set expires
     *
     * @param \DateTime $expires
     * @return OAuthRefreshToken
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * Get expires
     *
     * @return \DateTime
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set scope
     *
     * @param string $scope
     * @return OAuthRefreshToken
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set client
     *
     * @param OAuthClient $client
     * @return OAuthRefreshToken
     */
    public function setClient(OAuthClient $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return OAuthClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set user
     *
     * @param OAuthEmailRecipient $user
     * @return OAuthRefreshToken
     */
    public function setUser(OAuthEmailRecipient $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return OAuthEmailRecipient
     */
    public function getUser()
    {
        return $this->user;
    }

    public function toArray()
    {
        return [
            'refresh_token' => $this->refresh_token,
            'client_id' => $this->getClientId(),
            'user_id' => $this->user_id,
            'expires' => $this->expires,
            'scope' => $this->scope,
        ];
    }

    public static function fromArray($params)
    {
        $token = new self();
        foreach ($params as $property => $value) {
            $token->$property = $value;
        }
        return $token;
    }
}
