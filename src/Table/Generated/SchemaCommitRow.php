<?php

namespace Fusio\Impl\Table\Generated;

class SchemaCommitRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $schemaId = null;
    private ?int $userId = null;
    private ?string $prevHash = null;
    private ?string $commitHash = null;
    private ?string $source = null;
    private ?\PSX\DateTime\LocalDateTime $insertDate = null;
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getId(): int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setSchemaId(int $schemaId): void
    {
        $this->schemaId = $schemaId;
    }
    public function getSchemaId(): int
    {
        return $this->schemaId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "schema_id" was provided');
    }
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
    public function getUserId(): int
    {
        return $this->userId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "user_id" was provided');
    }
    public function setPrevHash(string $prevHash): void
    {
        $this->prevHash = $prevHash;
    }
    public function getPrevHash(): string
    {
        return $this->prevHash ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "prev_hash" was provided');
    }
    public function setCommitHash(string $commitHash): void
    {
        $this->commitHash = $commitHash;
    }
    public function getCommitHash(): string
    {
        return $this->commitHash ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "commit_hash" was provided');
    }
    public function setSource(string $source): void
    {
        $this->source = $source;
    }
    public function getSource(): string
    {
        return $this->source ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "source" was provided');
    }
    public function setInsertDate(\PSX\DateTime\LocalDateTime $insertDate): void
    {
        $this->insertDate = $insertDate;
    }
    public function getInsertDate(): \PSX\DateTime\LocalDateTime
    {
        return $this->insertDate ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "insert_date" was provided');
    }
    public function toRecord(): \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('schema_id', $this->schemaId);
        $record->put('user_id', $this->userId);
        $record->put('prev_hash', $this->prevHash);
        $record->put('commit_hash', $this->commitHash);
        $record->put('source', $this->source);
        $record->put('insert_date', $this->insertDate);
        return $record;
    }
    public function jsonSerialize(): object
    {
        return (object) $this->toRecord()->getAll();
    }
    public static function from(array|\ArrayAccess $data): self
    {
        $row = new self();
        $row->id = isset($data['id']) && is_int($data['id']) ? $data['id'] : null;
        $row->schemaId = isset($data['schema_id']) && is_int($data['schema_id']) ? $data['schema_id'] : null;
        $row->userId = isset($data['user_id']) && is_int($data['user_id']) ? $data['user_id'] : null;
        $row->prevHash = isset($data['prev_hash']) && is_string($data['prev_hash']) ? $data['prev_hash'] : null;
        $row->commitHash = isset($data['commit_hash']) && is_string($data['commit_hash']) ? $data['commit_hash'] : null;
        $row->source = isset($data['source']) && is_string($data['source']) ? $data['source'] : null;
        $row->insertDate = isset($data['insert_date']) && $data['insert_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['insert_date']) : null;
        return $row;
    }
}