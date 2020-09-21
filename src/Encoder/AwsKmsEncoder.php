<?php

declare(strict_types=1);

namespace Kcs\SecureLink\Encoder;

use Aws\Kms\KmsClient;
use Kcs\SecureLink\Exception\InvalidSignatureException;
use Kcs\SecureLink\Util\Base64;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use function parse_str;
use function Safe\parse_url;
use function strpos;

class AwsKmsEncoder implements EncoderInterface
{
    private KmsClient $kmsClient;
    private string $keyId;
    private string $signatureId;

    public function __construct(KmsClient $kmsClient, string $dsn)
    {
        $this->kmsClient = $kmsClient;
        [$this->keyId, $this->signatureId] = $this->parseDsn($dsn);
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(string $content): array
    {
        $response = $this->kmsClient->encrypt([
            'EncryptionAlgorithm' => 'RSAES_OAEP_SHA_256',
            'KeyId' => $this->keyId,
            'Plaintext' => $content,
        ]);

        $encoded = Base64::urlEncode($response['CiphertextBlob']);
        $response = $this->kmsClient->sign([
            'SigningAlgorithm' => 'ECDSA_SHA_256',
            'KeyId' => $this->signatureId,
            'Message' => $content,
        ]);

        return [$encoded, Base64::urlEncode($response['Signature'])];
    }

    public function decrypt(string $message, ?string $signature = null): string
    {
        if ($signature === null) {
            throw new InvalidSignatureException('Signature invalid');
        }

        $response = $this->kmsClient->decrypt([
            'EncryptionAlgorithm' => 'RSAES_OAEP_SHA_256',
            'KeyId' => $this->keyId,
            'CiphertextBlob' => Base64::urlDecode($message),
        ]);

        $message = $response['Plaintext'];
        $response = $this->kmsClient->verify([
            'SigningAlgorithm' => 'ECDSA_SHA_256',
            'KeyId' => $this->signatureId,
            'Message' => $message,
            'Signature' => Base64::urlDecode($signature),
        ]);

        if (! $response['SignatureValid']) {
            throw new InvalidSignatureException('Signature invalid');
        }

        return $message;
    }

    public function supports(string $dsn): bool
    {
        $url = parse_url($dsn);
        if ($url === false) {
            return false;
        }

        return $this->parseDsn($dsn)[0] === $this->keyId;
    }

    /**
     * @return string[]
     */
    private function parseDsn(string $dsn): array
    {
        $toArn = static function (array $url): string {
            if (($url['scheme'] ?? null) === 'arn' && strpos($url['path'], 'aws:kms:') === 0) {
                return $url['scheme'] . ':' . $url['path'];
            }

            throw new InvalidConfigurationException('Not implemented yet.');
        };

        $url = parse_url($dsn);
        if ($url === false) {
            return ['', ''];
        }

        $arn = $toArn($url);

        parse_str($url['query'] ?? '', $query);
        if (empty($query['signature'])) {
            throw new InvalidConfigurationException('Signature key is required for aws kms encoder');
        }

        $signatureArn = $toArn(parse_url($query['signature']));

        return [$arn, $signatureArn];
    }
}
