<?php

namespace Bojaghi\BaseObject;

class Util
{
    public static function isImplements(string $class, string $interface): bool
    {
        return in_array($interface, class_implements($class), true);
    }
}
