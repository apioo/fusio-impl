<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Log_Error implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $message;
    /**
     * @var string|null
     */
    protected $trace;
    /**
     * @var string|null
     */
    protected $file;
    /**
     * @var int|null
     */
    protected $line;
    /**
     * @param int|null $message
     */
    public function setMessage(?int $message) : void
    {
        $this->message = $message;
    }
    /**
     * @return int|null
     */
    public function getMessage() : ?int
    {
        return $this->message;
    }
    /**
     * @param string|null $trace
     */
    public function setTrace(?string $trace) : void
    {
        $this->trace = $trace;
    }
    /**
     * @return string|null
     */
    public function getTrace() : ?string
    {
        return $this->trace;
    }
    /**
     * @param string|null $file
     */
    public function setFile(?string $file) : void
    {
        $this->file = $file;
    }
    /**
     * @return string|null
     */
    public function getFile() : ?string
    {
        return $this->file;
    }
    /**
     * @param int|null $line
     */
    public function setLine(?int $line) : void
    {
        $this->line = $line;
    }
    /**
     * @return int|null
     */
    public function getLine() : ?int
    {
        return $this->line;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('message' => $this->message, 'trace' => $this->trace, 'file' => $this->file, 'line' => $this->line), static function ($value) : bool {
            return $value !== null;
        });
    }
}
