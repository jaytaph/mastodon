<?php

declare(strict_types=1);

namespace App;

use App\Exception\IncorrectDataTypeException;
use App\Exception\InvalidIndexException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class JsonArray
{
    protected PropertyAccessor $propertyAccessor;
    protected array $data;


    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->disableExceptionOnInvalidIndex()
            ->getPropertyAccessor()
        ;

        $this->data = $data;
    }

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!$data) {
            $data = [];
        }

        return new self($data);
    }

    public function getString(string $path): string
    {
        if (!$this->propertyAccessor->isReadable($this->data, $path)) {
            throw new InvalidIndexException($path);
        }

        $value = $this->propertyAccessor->getValue($this->data, $path);
        if (!is_string($value) && !is_null($value)) {
            throw new IncorrectDataTypeException('string', gettype($value));
        }

        return $value;
    }

    public function getStringIfExists(string $path): ?string
    {
        if (!$this->propertyAccessor->isReadable($this->data, $path)) {
            return null;
        }

        $value = $this->propertyAccessor->getValue($this->data, $path);
        if (!is_string($value)) {
            throw new IncorrectDataTypeException('string', gettype($value));
        }

        return $value;
    }

    public function getInt(string $path): int
    {
        if (!$this->propertyAccessor->isReadable($this->data, $path)) {
            throw new InvalidIndexException($path);
        }

        $value = $this->propertyAccessor->getValue($this->data, $path);
        if (!is_int($value)) {
            throw new IncorrectDataTypeException('int', gettype($value));
        }

        return $value;
    }

    public function getIntIfExists(string $path): ?int
    {
        if (!$this->propertyAccessor->isReadable($this->data, $path)) {
            return null;
        }

        $value = $this->propertyAccessor->getValue($this->data, $path);
        if (!is_int($value) && !is_null($value)) {
            throw new IncorrectDataTypeException('int', gettype($value));
        }

        return $value;
    }
}
