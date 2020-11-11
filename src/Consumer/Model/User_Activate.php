<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;

/**
 * @Required({"token"})
 */
class User_Activate implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $token;
    /**
     * @param string|null $token
     */
    public function setToken(?string $token) : void
    {
        $this->token = $token;
    }
    /**
     * @return string|null
     */
    public function getToken() : ?string
    {
        return $this->token;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('token' => $this->token), static function ($value) : bool {
            return $value !== null;
        });
    }
}
