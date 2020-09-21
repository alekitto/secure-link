<?php declare(strict_types=1);

namespace Tests\Kcs\SecureLink;

use Kcs\SecureLink\Encoder\EncoderInterface;
use Kcs\SecureLink\Exception\InvalidSignatureException;
use Kcs\SecureLink\Handler;
use Kcs\SecureLink\Operation;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HandlerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var Handler\HandlerInterface|ObjectProphecy
     */
    private ObjectProphecy $handler1;

    /**
     * @var Handler\HandlerInterface|ObjectProphecy
     */
    private $handler2;

    /**
     * @var EncoderInterface|ObjectProphecy
     */
    private $encoder;

    /**
     * @var Handler
     */
    private Handler $handler;

    protected function setUp(): void
    {
        $this->handler1 = $this->prophesize(Handler\HandlerInterface::class);
        $this->handler2 = $this->prophesize(Handler\HandlerInterface::class);
        $this->encoder = $this->prophesize(EncoderInterface::class);

        $this->handler = new Handler([
            $this->handler1->reveal(),
            $this->handler2->reveal(),
        ], $this->encoder->reveal());
    }

    public function testHandleShouldCorrectlyHandleOperation(): void
    {
        $operation = new Operation('reset_password', '');

        $this->handler1->supports($operation)->willReturn(false);
        $this->handler2->supports($operation)->willReturn(true);
        $this->handler2->handle($operation)
            ->shouldBeCalled()
            ->willReturn(new Response());

        $this->handler->handle($operation);
    }

    public function testHandleShouldThrowIfNoHandlerSupportsTheOperation(): void
    {
        $operation = new Operation('reset_password', '');

        $this->handler1->supports($operation)->willReturn(false);
        $this->handler2->supports($operation)->willReturn(false);

        $this->expectException(NotFoundHttpException::class);
        $this->handler->handle($operation);
    }

    public function testHandleShouldThrowIfLinkIsExpired(): void
    {
        $operation = new Operation('reset_password', '', (new \DateTimeImmutable())->modify('-2 days'));

        $this->expectException(NotFoundHttpException::class);
        $this->handler->handle($operation);
    }

    public function testHandleRequestShouldDecryptThePayload(): void
    {
        $this->encoder->decrypt('payload', 'sign')->willReturn('{"type":"reset_password","target":"","until":null}');
        $operation = new Operation('reset_password', '');

        $this->handler1->supports($operation)->willReturn(true);
        $this->handler1->handle($operation)
            ->shouldBeCalled()
            ->willReturn(new Response());

        $this->handler->handleRequest(new Request(['payload' => 'payload', 'sign' => 'sign']));
    }

    public function testHandleRequestShouldThrowNotFoundOnMissingParameters(): void
    {
        $this->encoder->decrypt(Argument::cetera())->shouldNotBeCalled();
        $this->handler1->handle(Argument::any())->shouldNotBeCalled();

        $this->expectException(NotFoundHttpException::class);
        $this->handler->handleRequest(new Request(['sign' => 'sign']));
    }

    public function testHandleRequestShouldThrowNotFoundOnBadPayload(): void
    {
        $this->encoder->decrypt(Argument::cetera())->willThrow(InvalidSignatureException::class);
        $this->handler1->handle(Argument::any())->shouldNotBeCalled();

        $this->expectException(NotFoundHttpException::class);
        $this->handler->handleRequest(new Request(['payload' => 'payload', 'sign' => 'sign']));
    }
}
