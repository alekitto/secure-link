<?php declare(strict_types=1);

namespace Tests\Kcs\SecureLink;

use Kcs\SecureLink\Encoder\EncoderInterface;
use Kcs\SecureLink\Generator;
use Kcs\SecureLink\Operation;
use Kcs\SecureLink\Payload;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class GeneratorTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var EncoderInterface|ObjectProphecy
     */
    private ObjectProphecy $encoder;
    private Generator $generator;

    protected function setUp(): void
    {
        $this->encoder = $this->prophesize(EncoderInterface::class);
        $this->generator = new Generator($this->encoder->reveal());
    }

    public function testGenerateShouldSerializeAndEncodeOperation(): void
    {
        $operation = new Operation('reset_password', 'this_user@email.com');
        $this->encoder->encrypt(Argument::type('string'))->willReturn(['encrypted', 'signature']);

        self::assertEquals(new Payload('encrypted', 'signature'), $this->generator->generate($operation));
    }
}
