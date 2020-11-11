<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;

/**
 * @Required({"name", "email", "password"})
 */
class User_Register implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $name;
    /**
     * @var string|null
     */
    protected $email;
    /**
     * @var string|null
     */
    protected $password;
    /**
     * @var string|null
     */
    protected $captcha;
    /**
     * @param string|null $name
     */
    public function setName(?string $name) : void
    {
        $this->name = $name;
    }
    /**
     * @return string|null
     */
    public function getName() : ?string
    {
        return $this->name;
    }
    /**
     * @param string|null $email
     */
    public function setEmail(?string $email) : void
    {
        $this->email = $email;
    }
    /**
     * @return string|null
     */
    public function getEmail() : ?string
    {
        return $this->email;
    }
    /**
     * @param string|null $password
     */
    public function setPassword(?string $password) : void
    {
        $this->password = $password;
    }
    /**
     * @return string|null
     */
    public function getPassword() : ?string
    {
        return $this->password;
    }
    /**
     * @param string|null $captcha
     */
    public function setCaptcha(?string $captcha) : void
    {
        $this->captcha = $captcha;
    }
    /**
     * @return string|null
     */
    public function getCaptcha() : ?string
    {
        return $this->captcha;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('name' => $this->name, 'email' => $this->email, 'password' => $this->password, 'captcha' => $this->captcha), static function ($value) : bool {
            return $value !== null;
        });
    }
}
