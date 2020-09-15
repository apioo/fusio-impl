<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Cronjob implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var string|null
     * @Pattern("^[a-zA-Z0-9\-\_]{3,64}$")
     */
    protected $name;
    /**
     * @var string|null
     */
    protected $cron;
    /**
     * @var string|null
     */
    protected $action;
    /**
     * @var \DateTime|null
     */
    protected $executeDate;
    /**
     * @var int|null
     */
    protected $exitCode;
    /**
     * @var array<Cronjob_Error>|null
     */
    protected $errors;
    /**
     * @param int|null $id
     */
    public function setId(?int $id) : void
    {
        $this->id = $id;
    }
    /**
     * @return int|null
     */
    public function getId() : ?int
    {
        return $this->id;
    }
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
     * @param string|null $cron
     */
    public function setCron(?string $cron) : void
    {
        $this->cron = $cron;
    }
    /**
     * @return string|null
     */
    public function getCron() : ?string
    {
        return $this->cron;
    }
    /**
     * @param string|null $action
     */
    public function setAction(?string $action) : void
    {
        $this->action = $action;
    }
    /**
     * @return string|null
     */
    public function getAction() : ?string
    {
        return $this->action;
    }
    /**
     * @param \DateTime|null $executeDate
     */
    public function setExecuteDate(?\DateTime $executeDate) : void
    {
        $this->executeDate = $executeDate;
    }
    /**
     * @return \DateTime|null
     */
    public function getExecuteDate() : ?\DateTime
    {
        return $this->executeDate;
    }
    /**
     * @param int|null $exitCode
     */
    public function setExitCode(?int $exitCode) : void
    {
        $this->exitCode = $exitCode;
    }
    /**
     * @return int|null
     */
    public function getExitCode() : ?int
    {
        return $this->exitCode;
    }
    /**
     * @param array<Cronjob_Error>|null $errors
     */
    public function setErrors(?array $errors) : void
    {
        $this->errors = $errors;
    }
    /**
     * @return array<Cronjob_Error>|null
     */
    public function getErrors() : ?array
    {
        return $this->errors;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'name' => $this->name, 'cron' => $this->cron, 'action' => $this->action, 'executeDate' => $this->executeDate, 'exitCode' => $this->exitCode, 'errors' => $this->errors), static function ($value) : bool {
            return $value !== null;
        });
    }
}
