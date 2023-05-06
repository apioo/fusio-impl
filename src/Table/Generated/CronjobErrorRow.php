<?php

namespace Fusio\Impl\Table\Generated;

class CronjobErrorRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $cronjobId = null;
    private ?string $message = null;
    private ?string $trace = null;
    private ?string $file = null;
    private ?int $line = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setCronjobId(int $cronjobId) : void
    {
        $this->cronjobId = $cronjobId;
    }
    public function getCronjobId() : int
    {
        return $this->cronjobId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "cronjob_id" was provided');
    }
    public function setMessage(string $message) : void
    {
        $this->message = $message;
    }
    public function getMessage() : string
    {
        return $this->message ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "message" was provided');
    }
    public function setTrace(string $trace) : void
    {
        $this->trace = $trace;
    }
    public function getTrace() : string
    {
        return $this->trace ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "trace" was provided');
    }
    public function setFile(string $file) : void
    {
        $this->file = $file;
    }
    public function getFile() : string
    {
        return $this->file ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "file" was provided');
    }
    public function setLine(int $line) : void
    {
        $this->line = $line;
    }
    public function getLine() : int
    {
        return $this->line ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "line" was provided');
    }
    public function toRecord() : \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('cronjob_id', $this->cronjobId);
        $record->put('message', $this->message);
        $record->put('trace', $this->trace);
        $record->put('file', $this->file);
        $record->put('line', $this->line);
        return $record;
    }
    public function jsonSerialize() : object
    {
        return (object) $this->toRecord()->getAll();
    }
    public static function from(array|\ArrayAccess $data) : self
    {
        $row = new self();
        $row->id = isset($data['id']) && is_int($data['id']) ? $data['id'] : null;
        $row->cronjobId = isset($data['cronjob_id']) && is_int($data['cronjob_id']) ? $data['cronjob_id'] : null;
        $row->message = isset($data['message']) && is_string($data['message']) ? $data['message'] : null;
        $row->trace = isset($data['trace']) && is_string($data['trace']) ? $data['trace'] : null;
        $row->file = isset($data['file']) && is_string($data['file']) ? $data['file'] : null;
        $row->line = isset($data['line']) && is_int($data['line']) ? $data['line'] : null;
        return $row;
    }
}