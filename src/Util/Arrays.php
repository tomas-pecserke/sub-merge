<?php

namespace App\Util;

use PhpParser\Builder\EnumCase;
use ReflectionEnum;
use ReflectionException;

class Arrays {
    private function __construct() {
    }

    /**
     * @template T
     * @template V
     * @param T[] $array
     * @param callable<T,V> $fn
     * @return array<T,V>
     */
    public static function mapTo(array $array, callable $fn): array {
        return array_combine($array, array_map($fn, $array));
    }

    public static function sumAssoc(array $x, array $y): array {
        $keys = array_unique(array_merge(array_keys($x), array_keys($y)));

        return self::mapTo($keys, fn($key) => (empty($x[$key]) ? 0 : $x[$key]) + (empty($y[$key]) ? 0 : $y[$key]));
    }
}
