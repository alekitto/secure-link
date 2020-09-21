<?php declare(strict_types=1);

namespace Tests\Kcs\SecureLink\Encoder;

use Kcs\SecureLink\Encoder\PlaintextEncoder;
use Kcs\SecureLink\Util\Base64;
use PHPUnit\Framework\TestCase;

class PlaintextEncoderTest extends TestCase
{
    private PlaintextEncoder $encoder;

    protected function setUp(): void
    {
        $this->encoder = new PlaintextEncoder();
    }

    public function testDecrypt(): void
    {
        self::assertEquals('foobar', $this->encoder->decrypt(Base64::urlEncode('foobar'), \hash('sha1', 'foobar')));
    }

    public function testEncrypt(): void
    {
        self::assertEquals([Base64::urlEncode('foobar'), \hash('sha1', 'foobar')], $this->encoder->encrypt('foobar'));
    }

    public function testSupportsShouldReturnTrueIfKeyIdIsTheSame(): void
    {
        self::assertTrue($this->encoder->supports('plaintext://'));
    }
}
