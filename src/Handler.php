<?php

declare(strict_types=1);

namespace Kcs\SecureLink;

use Kcs\SecureLink\Encoder\EncoderInterface;
use Safe\DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use function Safe\json_decode;
use const JSON_THROW_ON_ERROR;

class Handler implements RequestHandlerInterface
{
    /** @var Handler\HandlerInterface[] */
    private iterable $handlers;
    private EncoderInterface $encoder;

    /**
     * @param iterable<Handler\HandlerInterface> $handlers
     */
    public function __construct(iterable $handlers, EncoderInterface $encoder)
    {
        $this->handlers = $handlers;
        $this->encoder = $encoder;
    }

    public function handleRequest(Request $request): Response
    {
        $payload = $request->query->get('payload');
        $signature = $request->query->get('sign');

        if ($payload === null) {
            throw new NotFoundHttpException('Mandatory parameters missing');
        }

        try {
            $decoded = json_decode($this->encoder->decrypt($payload, $signature), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new NotFoundHttpException('Not Found.', $e);
        }

        $operation = new Operation($decoded['type'], $decoded['target'], $decoded['until']);

        return $this->handle($operation);
    }

    public function handle(Operation $operation): Response
    {
        if ($operation->validUntil !== null) {
            $validity = $operation->validUntil;
            if ($validity < new DateTimeImmutable()) {
                throw new NotFoundHttpException('Expired link');
            }
        }

        foreach ($this->handlers as $handler) {
            if (! $handler->supports($operation)) {
                continue;
            }

            return $handler->handle($operation);
        }

        throw new NotFoundHttpException('Unable to handle current operation.');
    }
}
