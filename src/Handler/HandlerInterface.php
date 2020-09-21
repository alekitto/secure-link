<?php

declare(strict_types=1);

namespace Kcs\SecureLink\Handler;

use Kcs\SecureLink\Operation;
use Symfony\Component\HttpFoundation\Response;

interface HandlerInterface
{
    /**
     * Handles a secure operation.
     */
    public function handle(Operation $operation): Response;

    /**
     * Checks if the handler supports the current operation.
     */
    public function supports(Operation $operation): bool;
}
