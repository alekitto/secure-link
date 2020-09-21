<?php

declare(strict_types=1);

namespace Kcs\SecureLink;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface RequestHandlerInterface extends HandlerInterface
{
    public function handleRequest(Request $request): Response;
}
