<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Model;

interface HasRouteNameInterface
{
    public function setRouteName(string $routeName): static;
}
