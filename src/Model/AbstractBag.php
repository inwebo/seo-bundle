<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Model;

abstract class AbstractBag implements BagInterface
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        private array $parameters = [],
    ) {
    }

    public function clear(): void
    {
        $this->parameters = [];
    }

    public function add(array $parameters): void
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    public function get(string $key): mixed
    {
        if (isset($this->parameters[$key])) {
            return $this->parameters[$key];
        }

        return null;
    }

    public function all(): array
    {
        return $this->parameters;
    }

    public function set(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->parameters);
    }
}
