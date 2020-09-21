<?php

declare(strict_types=1);

namespace Kcs\SecureLink\Routing;

use Kcs\SecureLink\Generator;
use Kcs\SecureLink\Operation;
use Safe\DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class Router implements RouterInterface, RequestMatcherInterface, WarmableInterface
{
    /** @var RouterInterface & RequestMatcherInterface & WarmableInterface */
    private RouterInterface $baseRouter;
    private Generator $secureLinkGenerator;

    public function __construct(RouterInterface $baseRouter, Generator $secureLinkGenerator)
    {
        $this->baseRouter = $baseRouter;
        $this->secureLinkGenerator = $secureLinkGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if (isset($parameters['_secure'])) {
            $operation = $parameters['_secure']['operation'] ?? null;
            $target = $parameters['_secure']['target'] ?? null;
            $validUntil = isset($parameters['_secure']['valid_until']) ?
                new DateTimeImmutable($parameters['_secure']['valid_until']) :
                (new DateTimeImmutable())->modify('+30 days');
            $operation = new Operation($operation, (string) $target, $validUntil);

            return $this->baseRouter->generate('secure_link', $this->secureLinkGenerator->generate($operation)->jsonSerialize(), $referenceType);
        }

        return $this->baseRouter->generate($name, $parameters, $referenceType);
    }

    public function setContext(RequestContext $context): void
    {
        $this->baseRouter->setContext($context);
    }

    public function getContext(): RequestContext
    {
        return $this->baseRouter->getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function matchRequest(Request $request): array
    {
        return $this->baseRouter->matchRequest($request);
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->baseRouter->getRouteCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function match(string $pathinfo): array
    {
        return $this->baseRouter->match($pathinfo);
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp(string $cacheDir)
    {
        return $this->baseRouter->warmUp($cacheDir);
    }
}
