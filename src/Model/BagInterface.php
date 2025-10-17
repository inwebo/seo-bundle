<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Model;

interface BagInterface
{
    public static function create(): BagInterface;

    public static function clear(): void;

    public static function add(array $parameter): void;

    public static function get(string $name): mixed;

    public static function all(): array;

    public static function set(string $name, mixed $value): void;

    public static function has(string $name): bool;

    public function __wakeup();
}
