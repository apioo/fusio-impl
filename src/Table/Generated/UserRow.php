<?php

namespace Fusio\Impl\Table\Generated;

class UserRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setRoleId(?int $roleId) : void
    {
        $this->setProperty('role_id', $roleId);
    }
    public function getRoleId() : ?int
    {
        return $this->getProperty('role_id');
    }
    public function setProvider(?int $provider) : void
    {
        $this->setProperty('provider', $provider);
    }
    public function getProvider() : ?int
    {
        return $this->getProperty('provider');
    }
    public function setStatus(?int $status) : void
    {
        $this->setProperty('status', $status);
    }
    public function getStatus() : ?int
    {
        return $this->getProperty('status');
    }
    public function setRemoteId(?string $remoteId) : void
    {
        $this->setProperty('remote_id', $remoteId);
    }
    public function getRemoteId() : ?string
    {
        return $this->getProperty('remote_id');
    }
    public function setName(?string $name) : void
    {
        $this->setProperty('name', $name);
    }
    public function getName() : ?string
    {
        return $this->getProperty('name');
    }
    public function setEmail(?string $email) : void
    {
        $this->setProperty('email', $email);
    }
    public function getEmail() : ?string
    {
        return $this->getProperty('email');
    }
    public function setPassword(?string $password) : void
    {
        $this->setProperty('password', $password);
    }
    public function getPassword() : ?string
    {
        return $this->getProperty('password');
    }
    public function setPoints(?int $points) : void
    {
        $this->setProperty('points', $points);
    }
    public function getPoints() : ?int
    {
        return $this->getProperty('points');
    }
    public function setToken(?string $token) : void
    {
        $this->setProperty('token', $token);
    }
    public function getToken() : ?string
    {
        return $this->getProperty('token');
    }
    public function setDate(?\DateTime $date) : void
    {
        $this->setProperty('date', $date);
    }
    public function getDate() : ?\DateTime
    {
        return $this->getProperty('date');
    }
}