<?php

namespace Ftp\MvcAuth\Entity;

use Doctrine\ORM\Mapping as ORM;
use ZF\MvcAuth\Identity\AuthenticatedIdentity;

/**
 * Class OAuthEmailRecipient
 * @ORM\Entity
 * @ORM\Table(name="OAUTH_USER")
 */
class OAuthEmailRecipient extends AuthenticatedIdentity
{

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string",name="ID")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return OAuthEmailRecipient
     */
    public function setEmail(string $email): OAuthEmailRecipient
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getRoleId(): ?string
    {
        return $this->email;
    }

    public function toArray()
    {
        return [
            'user_id' => $this->id,
            'scope' => null,
        ];
    }
}
