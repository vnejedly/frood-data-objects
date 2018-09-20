<?php
namespace Hooloovoo\DataObjects\Field;

use Hooloovoo\DataObjects\DataObjectInterface;
use Hooloovoo\DataObjects\Exception\NonCompatibleFieldsException;
use Hooloovoo\DataObjects\Field\Exception\InvalidValueException;

/**
 * Class FieldCollection
 */
class FieldCollection extends AbstractField
{
    const TYPE = 'array';

    /** @var DataObjectInterface[] */
    protected $_value;

    /**
     * @param DataObjectInterface[] $value
     */
    protected function _setValue($value = null)
    {
        if (is_null($value)) {
            $this->_value = null;
            return;
        }

        if (!is_array($value)) {
            throw new InvalidValueException(self::class, $value);
        }

        foreach ($value as $member) {
            if (!$member instanceof DataObjectInterface) {
                throw new InvalidValueException(self::class, $value);
            }
        }

        $this->_value = $value;
    }

    /**
     * @return bool
     */
    public function isUnlocked(): bool
    {
        if (is_null($this->_value)) {
            return $this->_unlocked;
        }

        foreach ($this->_value as $child) {
            if ($child->isUnlocked()) {
                return true;
            }
        }

        return $this->_unlocked;
    }

    /**
     * @return array
     */
    public function getSerialized()
    {
        $result = [];
        foreach ($this->_value as $dataObject) {
            $result[] = $dataObject->getSerialized();
        }

        return $result;
    }

    /**
     * @return DataObjectInterface
     */
    public function getSingle()
    {
        $value = $this->_value;
        return array_shift($value);
    }

    /**
     * @param FieldInterface $field
     * @param bool $direction
     * @return int
     */
    public function compareWith(FieldInterface $field, bool $direction): int
    {
        if (!$field instanceof self) {
            throw new NonCompatibleFieldsException($this, $field);
        }

        return $this->numberCompare(count($this->getValue()), count($field->getValue()), $direction);
    }
}