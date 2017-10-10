<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 18/09/2017
 * Time: 11:08
 */

namespace Ftp\MvcAuth\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OAuthClient
 * @ORM\Entity(repositoryClass="Ftp\MvcAuth\OAuth2\Storage\OAuthClientStorage")
 * @ORM\Table(name="OAUTH_CLIENT")
 */
class OAuthClient
{
    use EncryptableFieldEntityTrait;

    /**
     * @var integer
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\SequenceGenerator(sequenceName="OAUTH_CLIENT_ID")
     * @ORM\Column(type="integer", name="ID")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string",length=60,unique=true)
     */
    private $client_identifier;

    /**
     * @var string
     * @ORM\Column(type="string",length=60)
     */
    private $client_secret;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    private $redirect_uri;

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
     * Set client_identifier
     *
     * @param string $clientIdentifier
     * @return OAuthClient
     */
    public function setClientIdentifier($clientIdentifier)
    {
        $this->client_identifier = $clientIdentifier;
        return $this;
    }

    /**
     * Get client_identifier
     *
     * @return string
     */
    public function getClientIdentifier()
    {
        return $this->client_identifier;
    }

    /**
     * Set client_secret
     *
     * @param string $clientSecret
     * @return OAuthClient
     */
    public function setClientSecret($clientSecret)
    {
        $this->client_secret = $this->encryptField($clientSecret);
        return $this;
    }

    /**
     * Get client_secret
     *
     * @return string
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }

    /**
     * Verify client's secret
     *
     * @param string $password
     * @return Boolean
     */
    public function verifyClientSecret($clientSecret)
    {
        return $this->verifyEncryptedFieldValue($this->getClientSecret(), $clientSecret);
    }

    /**
     * Set redirect_uri
     *
     * @param string $redirectUri
     * @return OAuthClient
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirect_uri = $redirectUri;
        return $this;
    }

    /**
     * Get redirect_uri
     *
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    public function toArray()
    {
        return [
            'client_id' => $this->client_identifier,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $this->redirect_uri,
        ];
    }
}
