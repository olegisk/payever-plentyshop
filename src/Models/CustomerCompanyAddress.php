<?php

namespace Payever\Models;

use Plenty\Modules\Plugin\DataBase\Contracts\Model;

/**
 * @property int $id
 * @property string $addressHash
 * @property string $company
 */
class CustomerCompanyAddress extends Model
{
    /**
     * @var string
     */
    protected $primaryKeyFieldName = 'id';

    /**
     * @var string
     */
    protected $primaryKeyFieldType = self::FIELD_TYPE_INT;

    /**
     * @var bool
     */
    protected $autoIncrementPrimaryKey = true;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $addressHash;

    /**
     * @var string
     */
    public $company;

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'Payever::CustomerCompanyAddress';
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAddressHash(): string
    {
        return $this->addressHash;
    }

    /**
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * @param $hash
     * @return $this
     */
    public function setAddressHash($hash): self
    {
        $this->addressHash = $hash;

        return $this;
    }

    /**
     * @param $company
     * @return $this
     */
    public function setCompany($company): self
    {
        $this->company = $company;

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function attributesToArray(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAttribute($key)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeValue($key)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function hasGetMutator($key): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function setAttribute($key, $value): Model
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasSetMutator($key): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function fillJsonAttribute($key, $value): Model
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fromJson($value, $asObject = false)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function fromDateTime($value): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getDates(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function setDateFormat($format): Model
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasCast($key, $types = null): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getCasts(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function setRawAttributes($attributes, $sync = false): Model
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOriginal($key = null, $default = null)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function only($attributes): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function syncOriginal(): Model
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function syncOriginalAttribute($attribute): Model
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function syncChanges(): Model
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isDirty($attributes = null): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isClean($attributes = null): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function wasChanged($attributes = null): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getDirty(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getChanges(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getMutatedAttributes(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function cacheMutatedAttributes($class)
    {
        return null;
    }

    public function relationLoaded()
    {
        return null;
    }
}
