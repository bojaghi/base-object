<?php

namespace Bojaghi\BaseObject;

use Bojaghi\BaseObject\Attributes\Origin\Origin;
use Bojaghi\BaseObject\Attributes\Origin\PostOrigin;
use Bojaghi\BaseObject\Attributes\Field\Field;
use Bojaghi\BaseObject\Attributes\Primary;
use ReflectionClass;
use ReflectionProperty;

abstract class BaseObject
{
    /**
     * @uses PostOrigin::delete()
     */
    public function delete(bool $force = false): void
    {
        $origin  = static::getOriginAttribute();
        $primary = static::getPrimaryProperty();

        if ($origin && $primary) {
            $origin->delete($this->$primary, $force);
        }
    }

    protected static function getOriginAttribute(): ?Origin
    {
        static $cached = [];

        if (!isset($cached[static::class])) {
            $reflection    = new ReflectionClass(static::class);
            $foundDispatch = null;

            // Find the dispatch of the class
            foreach ($reflection->getAttributes() as $attribute) {
                if (Util::isImplements($attribute->getName(), Origin::class)) {
                    $foundDispatch = $attribute->newInstance();
                    break;
                }
            }

            $cached[static::class] = $foundDispatch;
        } else {
            $foundDispatch = $cached[static::class];
        }

        return $foundDispatch;
    }

    protected static function getPrimaryProperty(): string
    {
        static $cached = [];

        if (!isset($cached[static::class])) {
            $reflection    = new ReflectionClass(static::class);
            $foundProperty = '';

            // Find primary attribute
            foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
                foreach ($property->getAttributes() as $attribute) {
                    if (Util::isImplements($attribute->getName(), Primary::class)) {
                        $foundProperty = $property->getName();
                        break 2;
                    }
                }
            }

            if (!$foundProperty && property_exists(static::class, 'id')) {
                $foundProperty = 'id';
            }

            $cached[static::class] = $foundProperty;
        } else {
            $foundProperty = $cached[static::class];
        }

        return $foundProperty;
    }

    public static function get($id): static
    {
        $instance = new static();
        $origin   = static::getOriginAttribute();

        if ($origin) {
            $array = $origin->get($id, self::getFieldAttributes());
            foreach ($array as $key => $value) {
                $instance->$key = $value;
            }
        }

        return $instance;
    }

    public static function query(string|array $args = []): QueryResult
    {
        $origin = static::getOriginAttribute();
        if ($origin) {
            return $origin->query(wp_parse_args($args));
        } else {
            return new QueryResult();
        }
    }

    /**
     * @return array<string, Field>
     */
    protected static function getFieldAttributes(): array
    {
        static $cached = [];

        if (!isset($cached[static::class])) {
            $reflection = new ReflectionClass(static::class);
            $attributes = [];

            // Get mapping
            foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
                foreach ($property->getAttributes() as $attribute) {
                    if (Util::isImplements($attribute->getName(), Field::class)) {
                        $attributes[$property->getName()] = $attribute->newInstance();
                    }
                }
            }

            $cached[static::class] = $attributes;
        } else {
            $attributes = $cached[static::class];
        }

        return $attributes;
    }

    public function save(): void
    {
        $origin  = static::getOriginAttribute();
        $primary = static::getPrimaryProperty();

        if ($origin && $primary) {
            $theId = $origin->set($this->$primary, get_object_vars($this), static::getFieldAttributes());
            if ($theId) {
                $this->$primary = $theId;
            }
        }
    }
}
