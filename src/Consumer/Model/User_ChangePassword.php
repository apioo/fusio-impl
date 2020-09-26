<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;

/**
 * @Required({"token"})
 */
class User_ChangePassword implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $oldPassword;
    /**
     * @var string|null
     */
    protected $newPassword;
    /**
     * @var string|null
     */
    protected $verifyPassword;
    /**
     * @param string|null $oldPassword
     */
    public function setOldPassword(?string $oldPassword) : void
    {
        $this->oldPassword = $oldPassword;
    }
    /**
     * @return string|null
     */
    public function getOldPassword() : ?string
    {
        return $this->oldPassword;
    }
    /**
     * @param string|null $newPassword
     */
    public function setNewPassword(?string $newPassword) : void
    {
        $this->newPassword = $newPassword;
    }
    /**
     * @return string|null
     */
    public function getNewPassword() : ?string
    {
        return $this->newPassword;
    }
    /**
     * @param string|null $verifyPassword
     */
    public function setVerifyPassword(?string $verifyPassword) : void
    {
        $this->verifyPassword = $verifyPassword;
    }
    /**
     * @return string|null
     */
    public function getVerifyPassword() : ?string
    {
        return $this->verifyPassword;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('oldPassword' => $this->oldPassword, 'newPassword' => $this->newPassword, 'verifyPassword' => $this->verifyPassword), static function ($value) : bool {
            return $value !== null;
        });
    }
}