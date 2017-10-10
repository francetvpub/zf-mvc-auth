<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 18/09/2017
 * Time: 11:20
 */

namespace Ftp\MvcAuth\Entity;

trait EncryptableFieldEntityTrait
{
    protected $hashOptions = ['cost' => 11];

    protected function encryptField($value)
    {
        return password_hash($value, PASSWORD_BCRYPT, $this->hashOptions);
    }

    protected function verifyEncryptedFieldValue($encryptedValue, $value)
    {
        return password_verify($value, $encryptedValue);
    }
}
