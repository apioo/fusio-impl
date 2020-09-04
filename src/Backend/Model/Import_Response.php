<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Import_Response implements \JsonSerializable
{
    /**
     * @var bool|null
     */
    protected $success;
    /**
     * @var string|null
     */
    protected $message;
    /**
     * @var array<string>|null
     */
    protected $result;
    /**
     * @param bool|null $success
     */
    public function setSuccess(?bool $success) : void
    {
        $this->success = $success;
    }
    /**
     * @return bool|null
     */
    public function getSuccess() : ?bool
    {
        return $this->success;
    }
    /**
     * @param string|null $message
     */
    public function setMessage(?string $message) : void
    {
        $this->message = $message;
    }
    /**
     * @return string|null
     */
    public function getMessage() : ?string
    {
        return $this->message;
    }
    /**
     * @param array<string>|null $result
     */
    public function setResult(?array $result) : void
    {
        $this->result = $result;
    }
    /**
     * @return array<string>|null
     */
    public function getResult() : ?array
    {
        return $this->result;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('success' => $this->success, 'message' => $this->message, 'result' => $this->result), static function ($value) : bool {
            return $value !== null;
        });
    }
}
