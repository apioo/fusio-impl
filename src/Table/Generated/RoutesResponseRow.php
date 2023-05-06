<?php

namespace Fusio\Impl\Table\Generated;

class RoutesResponseRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $methodId = null;
    private ?int $code = null;
    private ?string $response = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setMethodId(int $methodId) : void
    {
        $this->methodId = $methodId;
    }
    public function getMethodId() : int
    {
        return $this->methodId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "method_id" was provided');
    }
    public function setCode(int $code) : void
    {
        $this->code = $code;
    }
    public function getCode() : int
    {
        return $this->code ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "code" was provided');
    }
    public function setResponse(string $response) : void
    {
        $this->response = $response;
    }
    public function getResponse() : string
    {
        return $this->response ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "response" was provided');
    }
    public function toRecord() : \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('method_id', $this->methodId);
        $record->put('code', $this->code);
        $record->put('response', $this->response);
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
        $row->methodId = isset($data['method_id']) && is_int($data['method_id']) ? $data['method_id'] : null;
        $row->code = isset($data['code']) && is_int($data['code']) ? $data['code'] : null;
        $row->response = isset($data['response']) && is_string($data['response']) ? $data['response'] : null;
        return $row;
    }
}