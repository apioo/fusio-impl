<?php

namespace Fusio\Impl\Table\Generated;

class LogErrorRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $logId = null;
    private ?string $message = null;
    private ?string $trace = null;
    private ?string $file = null;
    private ?int $line = null;
    private ?\PSX\DateTime\LocalDateTime $insertDate = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setLogId(int $logId) : void
    {
        $this->logId = $logId;
    }
    public function getLogId() : int
    {
        return $this->logId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "log_id" was provided');
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
    public function setInsertDate(?\PSX\DateTime\LocalDateTime $insertDate) : void
    {
        $this->insertDate = $insertDate;
    }
    public function getInsertDate() : ?\PSX\DateTime\LocalDateTime
    {
        return $this->insertDate;
    }
    public function toRecord() : \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('log_id', $this->logId);
        $record->put('message', $this->message);
        $record->put('trace', $this->trace);
        $record->put('file', $this->file);
        $record->put('line', $this->line);
        $record->put('insert_date', $this->insertDate);
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
        $row->logId = isset($data['log_id']) && is_int($data['log_id']) ? $data['log_id'] : null;
        $row->message = isset($data['message']) && is_string($data['message']) ? $data['message'] : null;
        $row->trace = isset($data['trace']) && is_string($data['trace']) ? $data['trace'] : null;
        $row->file = isset($data['file']) && is_string($data['file']) ? $data['file'] : null;
        $row->line = isset($data['line']) && is_int($data['line']) ? $data['line'] : null;
        $row->insertDate = isset($data['insert_date']) && $data['insert_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['insert_date']) : null;
        return $row;
    }
}