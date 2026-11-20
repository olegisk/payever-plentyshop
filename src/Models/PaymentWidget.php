<?php

namespace Payever\Models;

use Plenty\Modules\Plugin\DataBase\Contracts\Model;

/**
 * @property string $id
 * @property string $type
 * @property string $name
 * @property string $businessId
 * @property string $checkoutId
 * @property array $payments
 */
class PaymentWidget extends Model
{
    /**
     * @var string
     */
    protected $primaryKeyFieldName = 'id';

    /**
     * @var string
     */
    protected $primaryKeyFieldType = self::FIELD_TYPE_STRING;

    /**
     * @var bool
     */
    protected $autoIncrementPrimaryKey = false;

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $businessId;

    /**
     * @var string
     */
    public $checkoutId;

    /**
     * @var array
     */
    public $payments = [];

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'Payever::Widget';
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
        return;
    }

    /**
     * @inheritDoc
     */
    public function getAttributeValue($key)
    {
        return;
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
        return;
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
        return;
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
        return;
    }

    public function relationLoaded()
    {
        return;
    }
}
