<?php declare(strict_types=1);

namespace Tests\Kcs\SecureLink\Encoder;

use Aws\Kms\KmsClient;
use Kcs\SecureLink\Encoder\AwsKmsEncoder;
use Kcs\SecureLink\Util\Base64;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class AwsKmsEncoderTest extends TestCase
{
    use ProphecyTrait;

    private const KEY_ARN = 'arn:aws:kms:eu-west-1:1234567890123:key/21120c01-abdb-48fc-8a9f-fd0cdcba02bc';
    private const SIGNATURE_ARN = 'arn:aws:kms:eu-west-1:1234567890123:key/60961e8a-9c9e-42cf-be06-ec7a6f70d336';

    /**
     * @var KmsClient|ObjectProphecy
     */
    private ObjectProphecy $client;
    private AwsKmsEncoder $encoder;

    protected function setUp(): void
    {
        $this->client = $this->prophesize(KmsClient::class);
        $this->encoder = new AwsKmsEncoder($this->client->reveal(), self::KEY_ARN.'?signature='.self::SIGNATURE_ARN);
    }

    public function testDecrypt(): void
    {
        $this->client->decrypt([
            'EncryptionAlgorithm' => 'RSAES_OAEP_SHA_256',
            'KeyId' => self::KEY_ARN,
            'CiphertextBlob' => 'foobar_encrypted',
        ])->willReturn(['Plaintext' => 'foobar']);

        $this->client->verify([
            'SigningAlgorithm' => 'ECDSA_SHA_256',
            'KeyId' => self::SIGNATURE_ARN,
            'Message' => 'foobar',
            'Signature' => 'signature',
        ])->willReturn(['SignatureValid' => true]);

        self::assertEquals('foobar', $this->encoder->decrypt(Base64::urlEncode('foobar_encrypted'), Base64::urlEncode('signature')));
    }

    public function testEncrypt(): void
    {
        $this->client->encrypt([
            'EncryptionAlgorithm' => 'RSAES_OAEP_SHA_256',
            'KeyId' => self::KEY_ARN,
            'Plaintext' => 'foobar',
        ])->willReturn(['CiphertextBlob' => 'foobar_encrypted']);

        $this->client->sign([
            'SigningAlgorithm' => 'ECDSA_SHA_256',
            'KeyId' => self::SIGNATURE_ARN,
            'Message' => 'foobar',
        ])->willReturn(['Signature' => '123456789abcdef']);

        self::assertEquals([Base64::urlEncode('foobar_encrypted'), Base64::urlEncode('123456789abcdef')], $this->encoder->encrypt('foobar'));
    }

    public function testSupportsShouldReturnTrueIfKeyIdIsTheSame(): void
    {
        self::assertTrue($this->encoder->supports(self::KEY_ARN.'?signature='.self::SIGNATURE_ARN));
    }
}
