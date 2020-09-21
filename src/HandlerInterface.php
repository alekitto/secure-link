<?php

declare(strict_types=1);

namespace Kcs\SecureLink;

use Symfony\Component\HttpFoundation\Response;

interface HandlerInterface
{
    /**
     * Handles a secure link operation.
     */
    public function handle(Operation $operation): Response;
}
