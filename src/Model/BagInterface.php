<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Model;

interface BagInterface
{
    public function clear(): void;

    /**
     * @param array<string, mixed> $parameters
     */
    public function add(array $parameters): void;

    public function get(string $key): mixed;

    /**
     * @return array<string, mixed>
     */
    public function all(): array;

    public function set(string $key, mixed $value): void;

    public function has(string $key): bool;
}
