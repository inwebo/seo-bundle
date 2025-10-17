<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Tests\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class TestController extends AbstractController
{
    public function __invoke(int $page = 1): Response
    {
        return $this->render('');
    }
}
