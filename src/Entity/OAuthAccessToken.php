<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 18/09/2017
 * Time: 11:46
 */

namespace Ftp\MvcAuth\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OAuthClient
 * @ORM\Entity(repositoryClass="Ftp\MvcAuth\OAuth2\Storage\OAuthAccessTokenStorage")
 * @ORM\Table(name="OAUTH_ACCESS_TOKEN")
 */
class OAuthAccessToken
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\SequenceGenerator(sequenceName="OAUTH_ACCESS_TOKEN_ID")
     * @ORM\Column(type="integer", name="ID")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string",length=40,unique=true)
     */
    protected $token;

    /**
     * @var OAuthClient
     * @ORM\ManyToOne(targetEntity="Ftp\MvcAuth\Entity\OAuthClient")
     * @ORM\JoinColumn(name="CLIENT_ID", referencedColumnName="ID")
     */
    protected $client;

    /**
     * @var OAuthEmailRecipient
     * @ORM\ManyToOne(targetEntity="Ftp\MvcAuth\Entity\OAuthEmailRecipient")
     * @ORM\JoinColumn(name="USER_ID", referencedColumnName="ID")
     */
    protected $user;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $expires;

    /**
     * @var string
     * @ORM\Column(type="string",length=50,nullable=true)
     */
    protected $scope;

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
     * Set token
     *
     * @param string $token
     * @return OAuthAccessToken
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set expires
     *
     * @param \DateTime $expires
     * @return OAuthAccessToken
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
     * @return OAuthAccessToken
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
     * @return OAuthAccessToken
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
     * Get client_id
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->getClient() ? $this->getClient()->getId() : null;
    }

    public static function fromArray($params)
    {
        $token = new self();
        foreach ($params as $property => $value) {
            $token->$property = $value;
        }
        return $token;
    }

    /**
     * Set user
     *
     * @param OAuthEmailRecipient $user
     * @return OAuthAccessToken
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

    /**
     * Get user_identifier
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->getUser() ? $this->getUser()->getId() : null;
    }

    public function toArray()
    {
        return [
            'token' => $this->token,
            'client_id' => $this->getClientId(),
            'user_id' => $this->getUserId(),
            'expires' => $this->expires,
            'scope' => $this->scope,
        ];
    }
}
