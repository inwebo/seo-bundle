<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Model;

class BreadcrumbBag
{
    private static \SplFixedArray $instances;

    /**
     * @var array<string, mixed>
     */
    private static array $vars = [];

    /**
     * Protected constructor to enforce singleton usage.
     */
    public function __construct()
    {
        static::$instances = new \SplFixedArray(1);
    }

    /**
     * Returns the singleton instance for the current class.
     */
    public static function create(): static
    {
        $singleton = new static();
        if (0 === self::$instances->count()) {
            self::$instances[0] = $singleton;
        }

        return $singleton;
    }

    /**
     * Adds or overrides variables used by the breadcrumb rendering logic.
     *
     * @param array<string, mixed> $var Key-value pairs to merge into the registry
     */
    public static function addVar(array $var): void
    {
        self::$vars = array_merge(self::$vars, $var);
    }

    /**
     * Returns the currently registered variables.
     *
     * @return array<string, mixed>
     */
    public static function getVars(): array
    {
        return self::$vars;
    }

    /**
     * Disallow cloning for singleton usage.
     */
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
