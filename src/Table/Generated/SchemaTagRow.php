<?php

namespace Fusio\Impl\Table\Generated;

class SchemaTagRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $commitId = null;
    private ?int $userId = null;
    private ?string $version = null;
    private ?\PSX\DateTime\LocalDateTime $insertDate = null;
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getId(): int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setCommitId(int $commitId): void
    {
        $this->commitId = $commitId;
    }
    public function getCommitId(): int
    {
        return $this->commitId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "commit_id" was provided');
    }
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
    public function getUserId(): int
    {
        return $this->userId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "user_id" was provided');
    }
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }
    public function getVersion(): string
    {
        return $this->version ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "version" was provided');
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
        $record->put('commit_id', $this->commitId);
        $record->put('user_id', $this->userId);
        $record->put('version', $this->version);
        $record->put('insert_date', $this->insertDate);
        return $record;
    }
    public function jsonSerialize(): object
    {
        return (object) $this->toRecord()->getAll();
    }
    /**
     * @param array<string, mixed>|\ArrayAccess<string, mixed> $data
     */
    public static function from(array|\ArrayAccess $data): self
    {
        $row = new self();
        $row->id = isset($data['id']) && is_int($data['id']) ? $data['id'] : null;
        $row->commitId = isset($data['commit_id']) && is_int($data['commit_id']) ? $data['commit_id'] : null;
        $row->userId = isset($data['user_id']) && is_int($data['user_id']) ? $data['user_id'] : null;
        $row->version = isset($data['version']) && is_string($data['version']) ? $data['version'] : null;
        $row->insertDate = isset($data['insert_date']) && $data['insert_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['insert_date']) : null;
        return $row;
    }
}