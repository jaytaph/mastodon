<?php

declare(strict_types=1);

namespace App;

use App\Exception\IncorrectDataTypeException;
use App\Exception\InvalidIndexException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class JsonArray implements \JsonSerializable
{
    protected PropertyAccessorInterface $propertyAccessor;

    /** @var mixed[] */
    protected array $data;

    /**
     * @param mixed[] $data
     */
    public function __construct(array $data)
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->enableExceptionOnInvalidPropertyPath()
            ->disableMagicCall()
            ->disableMagicGet()
            ->disableMagicMethods()
            ->disableMagicSet()
            ->getPropertyAccessor()
        ;

        $this->data = $data;
    }

    // Creates a new JsonArray from a JSON string
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            $data = [$data];
        }

        return new self($data);
    }

    /**
     * Returns the array in the JsonArray
     * @return mixed[]
     */
    public function toArray(): array
    {
        return $this->data;
    }

    // Returns the string at the given path, or $default if not found. If the path doesn't point to a string, it will throw an exception.
    public function getString(string $path, ?string $default = null): string
    {
        $value = $this->getValue($path, $default);
        if (!is_string($value)) {
            throw new IncorrectDataTypeException('string', gettype($value));
        }

        return $value;
    }

    // Returns the string at the given path, or null when the path is not found. If the value on path is not a string, it will throw an exception.
    public function getStringOrNull(string $path): ?string
    {
        if (!$this->propertyAccessor->isReadable($this->data, $path)) {
            return null;
        }

        $value = $this->propertyAccessor->getValue($this->data, $path);
        if (!is_string($value) && !is_null($value)) {
            throw new IncorrectDataTypeException('string', gettype($value));
        }

        return $value;
    }

    // Returns int from given path
    public function getInt(string $path, ?int $default = null): int
    {
        $value = $this->getValue($path, $default);
        if (!is_int($value)) {
            throw new IncorrectDataTypeException('int', gettype($value));
        }

        return $value;
    }

    // Returns int on given path, or null when not found
    public function getIntOrNull(string $path): ?int
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

    // Returns bool from given path
    public function getBool(string $path, bool $default = false): bool
    {
        $value = $this->getValue($path, $default);
        if (!is_bool($value)) {
            throw new IncorrectDataTypeException('bool', gettype($value));
        }

        return $value;
    }

    // Returns a JsonArray from the given path
    public function getJsonArray(string $path, ?JsonArray $default = null): JsonArray
    {
        $value = $this->getValue($path, $default);
        if (!is_array($value) && ! $value instanceof JsonArray) {
            throw new IncorrectDataTypeException('array', gettype($value));
        }

        return ($value instanceof JsonArray) ? $value : new JsonArray($value);
    }

    // Returns a JsonArray from the given path, or null when not found
    public function getJsonArrayOrNull(string $path): ?JsonArray
    {
        if (!$this->propertyAccessor->isReadable($this->data, $path)) {
            return null;
        }

        $value = $this->propertyAccessor->getValue($this->data, $path);
        if (!is_array($value) && !is_null($value)) {
            throw new IncorrectDataTypeException('array', gettype($value));
        }

        return new JsonArray($value ?? []);
    }

    // Returns true when the given path is an array or JsonArray
    public function isJsonArray(string $path): bool
    {
        $value = $this->propertyAccessor->getValue($this->data, $path);

        return is_array($value) || $value instanceof JsonArray;
    }

    // Returns an empty JsonArray
    public static function empty(): self
    {
        return new JsonArray([]);
    }

    // Returns true when the object is empty
    public function isEmpty(): bool
    {
        return count($this->data) === 0;
    }

    // REturns true when the given path exists
    public function exists(string $path): bool
    {
        return $this->propertyAccessor->isReadable($this->data, $path);
    }

    // Returns true when the given path does not exist or is null
    public function isNullOrNotExists(string $path): bool
    {
        if (!$this->propertyAccessor->isReadable($this->data, $path)) {
            return true;
        }

        $value = $this->propertyAccessor->getValue($this->data, $path);
        return is_null($value);
    }

    protected function getValue(string $path, mixed $default = null): mixed
    {
        if (!$this->propertyAccessor->isReadable($this->data, $path)) {
            if ($default !== null) {
                return $default;
            }
            throw new InvalidIndexException($path);
        }

        $value = $this->propertyAccessor->getValue($this->data, $path);
        if ($value === null && $default !== null) {
            return $default;
        }

        return $value;
    }

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}
