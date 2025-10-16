<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Model;

use Symfony\Component\Uid\UuidV6 as Uuid;

/**
 * BreadcrumbInterface defines the contract for a breadcrumb domain object.
 *
 * It exposes identifiers, labeling, hierarchy, and routing information used
 * to render SEO-friendly breadcrumb trails.
 *
 * Implementations should be immutable where possible, but setters are provided
 * for persistence layers and builders.
 */
interface BreadcrumbInterface
{
    /**
     * Returns the internal numeric identifier of the breadcrumb if it exists.
     *
     * Note: This is typically managed by the persistence layer.
     *
     * @return int|null the database identifier or null if not persisted yet
     */
    public function getId(): ?int;

    /**
     * Sets the internal numeric identifier.
     *
     * Note: Intended for ORM/persistence usage only.
     *
     * @param int|null $id the identifier to set
     */
    public function setId(?int $id): void;

    /**
     * Returns the unique UUID (version 6) identifier of the breadcrumb.
     *
     * @return Uuid|null the UUID value or null if not initialized
     */
    public function getUuid(): ?Uuid;

    /**
     * Gets the Symfony route name associated with this breadcrumb item.
     *
     * @return string|null the route name or null when not set
     */
    public function getRoute(): ?string;

    /**
     * Sets the Symfony route name associated with this breadcrumb.
     *
     * @param string $route the route name
     *
     * @return static fluent interface
     */
    public function setRoute(string $route): static;

    /**
     * Gets the human-readable label of the breadcrumb item.
     *
     * @return string|null the label to display, or null when not set
     */
    public function getName(): ?string;

    /**
     * Sets the human-readable label of the breadcrumb item.
     *
     * @param string $name the label to display
     *
     * @return static fluent interface
     */
    public function setName(string $name): static;

    /**
     * Returns the parent breadcrumb route name or identifier, if any.
     *
     * @return string|null the parent route name or null when this is a root item
     */
    public function getParent(): ?string;

    /**
     * Sets the parent breadcrumb route name or identifier.
     *
     * @param string|null $parent the parent route name; null for a root item
     *
     * @return static fluent interface
     */
    public function setParent(?string $parent): static;

    /**
     * Gets the SEO title associated with this breadcrumb, if any.
     *
     * @return string|null the title or null when not defined
     */
    public function getTitle(): ?string;

    /**
     * Sets the SEO title associated with this breadcrumb.
     *
     * @param string|null $title the title to set
     *
     * @return static fluent interface
     */
    public function setTitle(?string $title): static;

    /**
     * Returns the parameters used to generate the route URL for this breadcrumb.
     *
     * @return array<int, string> a list of parameter values (ordered), used for URL generation
     */
    public function getRouteParameters(): array;

    /**
     * Sets or merges parameters used to generate the route URL for this breadcrumb.
     *
     * Implementations may choose to merge with existing parameters rather than replace.
     *
     * @param array<int, string> $routeParameters the parameters to apply for URL generation
     *
     * @return static fluent interface
     */
    public function setRouteParameters(array $routeParameters): static;
}
