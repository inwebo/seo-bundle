<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping\Entity;
use Inwebo\SeoBundle\Entity\Metadata as BaseMetadata;

#[Entity]
class Metadata extends BaseMetadata
{
}
