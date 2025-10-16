<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Inwebo\SeoBundle\Model\BreadcrumbInterface;
use Symfony\Component\Uid\UuidV6 as Uuid;

#[ORM\MappedSuperclass]
/**
 * Breadcrumb is a mapped superclass implementing the BreadcrumbInterface.
 *
 * It represents a single breadcrumb item with identifiers, display label,
 * hierarchical relation (parent), and routing information used to build
 * SEO-friendly breadcrumb trails.
 *
 * Persistence notes:
 * - id is managed by the ORM as an auto-increment primary key.
 * - uuid is stored as a string representation of a UUIDv6 for uniqueness.
 */
class Breadcrumb implements BreadcrumbInterface
{
    public const string BREADCRUMB_KEY = 'breadcrumb';

    /**
     * Auto-incremented database identifier.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Canonical string representation of the UUIDv6 identifier.
     */
    #[ORM\Column(type: 'uuid', unique: true)]
    private string $uuid;

    /**
     * Symfony route name uniquely associated with this breadcrumb.
     */
    #[ORM\Column(length: 255, unique: true)]
    private ?string $route = null;

    /**
     * Human-readable label displayed for the breadcrumb item.
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * Optional parent breadcrumb route name or identifier.
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $parent = null;

    /**
     * Optional SEO title associated with the breadcrumb.
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    /**
     * Route parameters used for URL generation.
     *
     * @var array<int, string>
     */
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private array $routeParameters = [];

    /**
     * Initializes the breadcrumb with a freshly generated UUIDv6.
     */
    public function __construct()
    {
        $this->uuid = Uuid::generate();
    }

    /**
     * Returns the internal numeric identifier of the breadcrumb if it exists.
     *
     * Note: This is typically managed by the persistence layer.
     *
     * @return int|null the database identifier or null if not persisted yet
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Sets the internal numeric identifier.
     *
     * Note: Intended for ORM/persistence usage only.
     *
     * @param int|null $id the identifier to set
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * Returns the unique UUID (version 6) identifier of the breadcrumb.
     *
     * @return Uuid|null the UUID value or null if not initialized
     */
    public function getUuid(): ?Uuid
    {
        return Uuid::fromString($this->uuid);
    }

    /**
     * Gets the Symfony route name associated with this breadcrumb item.
     *
     * @return string|null the route name or null when not set
     */
    public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * Sets the Symfony route name associated with this breadcrumb.
     *
     * @param string $route the route name
     *
     * @return static fluent interface
     */
    public function setRoute(string $route): static
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Gets the human-readable label of the breadcrumb item.
     *
     * @return string|null the label to display, or null when not set
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets the human-readable label of the breadcrumb item.
     *
     * @param string $name the label to display
     *
     * @return static fluent interface
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the parent breadcrumb route name or identifier, if any.
     *
     * @return string|null the parent route name or null when this is a root item
     */
    public function getParent(): ?string
    {
        return $this->parent;
    }

    /**
     * Sets the parent breadcrumb route name or identifier.
     *
     * @param string|null $parent the parent route name; null for a root item
     *
     * @return static fluent interface
     */
    public function setParent(?string $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Gets the SEO title associated with this breadcrumb, if any.
     *
     * @return string|null the title or null when not defined
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Sets the SEO title associated with this breadcrumb.
     *
     * @param string|null $title the title to set
     *
     * @return static fluent interface
     */
    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Returns the parameters used to generate the route URL for this breadcrumb.
     *
     * @return array<int, string> a list of parameter values (ordered), used for URL generation
     */
    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    /**
     * Sets or merges parameters used to generate the route URL for this breadcrumb.
     *
     * Implementations may choose to merge with existing parameters rather than replace.
     *
     * @param array<int, string> $routeParameters the parameters to apply for URL generation
     *
     * @return static fluent interface
     */
    public function setRouteParameters(array $routeParameters): static
    {
        $this->routeParameters = array_merge($this->routeParameters, $routeParameters);

        return $this;
    }
}
