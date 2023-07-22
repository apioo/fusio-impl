<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\IdentityRow>
 */
class IdentityTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_identity';
    public const COLUMN_ID = 'id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_APP_ID = 'app_id';
    public const COLUMN_ROLE_ID = 'role_id';
    public const COLUMN_NAME = 'name';
    public const COLUMN_ICON = 'icon';
    public const COLUMN_CLASS = 'class';
    public const COLUMN_CLIENT_ID = 'client_id';
    public const COLUMN_CLIENT_SECRET = 'client_secret';
    public const COLUMN_AUTHORIZATION_URI = 'authorization_uri';
    public const COLUMN_TOKEN_URI = 'token_uri';
    public const COLUMN_USER_INFO_URI = 'user_info_uri';
    public const COLUMN_ID_PROPERTY = 'id_property';
    public const COLUMN_NAME_PROPERTY = 'name_property';
    public const COLUMN_EMAIL_PROPERTY = 'email_property';
    public const COLUMN_ALLOW_CREATE = 'allow_create';
    public const COLUMN_INSERT_DATE = 'insert_date';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x3020000a, self::COLUMN_STATUS => 0x20000a, self::COLUMN_APP_ID => 0x20000a, self::COLUMN_ROLE_ID => 0x4020000a, self::COLUMN_NAME => 0xa00080, self::COLUMN_ICON => 0xa00040, self::COLUMN_CLASS => 0xa000ff, self::COLUMN_CLIENT_ID => 0xa000ff, self::COLUMN_CLIENT_SECRET => 0xa000ff, self::COLUMN_AUTHORIZATION_URI => 0x40a000ff, self::COLUMN_TOKEN_URI => 0x40a000ff, self::COLUMN_USER_INFO_URI => 0x40a000ff, self::COLUMN_ID_PROPERTY => 0x40a000ff, self::COLUMN_NAME_PROPERTY => 0x40a000ff, self::COLUMN_EMAIL_PROPERTY => 0x40a000ff, self::COLUMN_ALLOW_CREATE => 0x400000, self::COLUMN_INSERT_DATE => 0x800000);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateById(int $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteById(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByStatus(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByStatus(int $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByStatus(int $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByStatus(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByAppId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('app_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByAppId(int $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('app_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByAppId(int $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('app_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByAppId(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('app_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByRoleId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('role_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByRoleId(int $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('role_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByRoleId(int $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('role_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByRoleId(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('role_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByName(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByName(string $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByName(string $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByName(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByIcon(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('icon', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByIcon(string $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('icon', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByIcon(string $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('icon', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByIcon(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('icon', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByClass(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('class', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByClass(string $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('class', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByClass(string $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('class', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByClass(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('class', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByClientId(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('client_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByClientId(string $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('client_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByClientId(string $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('client_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByClientId(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('client_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByClientSecret(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('client_secret', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByClientSecret(string $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('client_secret', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByClientSecret(string $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('client_secret', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByClientSecret(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('client_secret', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByAuthorizationUri(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('authorization_uri', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByAuthorizationUri(string $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('authorization_uri', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByAuthorizationUri(string $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('authorization_uri', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByAuthorizationUri(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('authorization_uri', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByTokenUri(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('token_uri', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByTokenUri(string $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('token_uri', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByTokenUri(string $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('token_uri', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByTokenUri(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('token_uri', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByUserInfoUri(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('user_info_uri', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByUserInfoUri(string $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('user_info_uri', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByUserInfoUri(string $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('user_info_uri', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByUserInfoUri(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('user_info_uri', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByIdProperty(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('id_property', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByIdProperty(string $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('id_property', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByIdProperty(string $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('id_property', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByIdProperty(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('id_property', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByNameProperty(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name_property', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByNameProperty(string $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name_property', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByNameProperty(string $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name_property', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByNameProperty(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name_property', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByEmailProperty(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('email_property', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByEmailProperty(string $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('email_property', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByEmailProperty(string $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('email_property', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByEmailProperty(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('email_property', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByAllowCreate(bool $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('allow_create', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByAllowCreate(bool $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('allow_create', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByAllowCreate(bool $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('allow_create', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByAllowCreate(bool $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('allow_create', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\IdentityRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByInsertDate(\PSX\DateTime\LocalDateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('insert_date', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByInsertDate(\PSX\DateTime\LocalDateTime $value) : ?\Fusio\Impl\Table\Generated\IdentityRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('insert_date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByInsertDate(\PSX\DateTime\LocalDateTime $value, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('insert_date', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByInsertDate(\PSX\DateTime\LocalDateTime $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('insert_date', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        return $this->doCreate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        return $this->doUpdate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBy(\PSX\Sql\Condition $condition, \Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\IdentityRow $record) : int
    {
        return $this->doDelete($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteBy(\PSX\Sql\Condition $condition) : int
    {
        return $this->doDeleteBy($condition);
    }
    /**
     * @param array<string, mixed> $row
     */
    protected function newRecord(array $row) : \Fusio\Impl\Table\Generated\IdentityRow
    {
        return \Fusio\Impl\Table\Generated\IdentityRow::from($row);
    }
}