<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Model;

class AbstractBag implements BagInterface
{
    private static \SplFixedArray $instances;

    /**
     * @var array<string, mixed>
     */
    private static array $parameters = [];

    protected function __construct()
    {
        static::$instances = new \SplFixedArray(1);
    }

    public static function create(): static
    {
        $singleton = new static();
        if (0 === self::$instances->count()) {
            self::$instances[0] = $singleton;
        }

        return $singleton;
    }

    public static function clear(): void
    {
        static::$parameters = [];
    }

    public static function add(array $parameter): void
    {
        self::$parameters = array_merge(self::$parameters, $parameter);
    }

    public static function get(string $name): mixed
    {
        return self::$parameters[$name];
    }

    public static function all(): array
    {
        return self::$parameters;
    }

    public static function set(string $name, mixed $value): void
    {
        self::$parameters[$name] = $value;
    }

    public static function has(string $name): bool
    {
        return array_key_exists($name, self::$parameters);
    }

    protected function __clone()
    {
    }

    /**
     * Prevent unserialization of the singleton instance.
     *
     * @throws \Exception always, to prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize '.__CLASS__);
    }
}
