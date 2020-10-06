<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Account_ChangePassword implements \JsonSerializable
{
    /**
     * @var string|null
     * @MinLength(8)
     * @MaxLength(128)
     */
    protected $oldPassword;
    /**
     * @var string|null
     * @MinLength(8)
     * @MaxLength(128)
     */
    protected $newPassword;
    /**
     * @var string|null
     * @MinLength(8)
     * @MaxLength(128)
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
