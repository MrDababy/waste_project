<?php
/**
 * Encryption Service
 * 
 * Implements AES-256-GCM authenticated encryption.
 */

namespace App\Services;

use App\Interfaces\EncryptionInterface;
use App\Core\Config;
use RuntimeException;

class EncryptionService implements EncryptionInterface
{
    /**
     * @var string Encryption key (binary)
     */
    private string $key;

    /**
     * @var string Cipher method
     */
    private string $cipher;

    /**
     * @var int IV length in bytes
     */
    private int $ivLength;

    /**
     * @var int Tag length in bytes
     */
    private int $tagLength;

    /**
     * Initialize encryption service
     * 
     * @param string|null $key Encryption key (binary). If null, load from config.
     * @throws RuntimeException If key is invalid or OpenSSL not available
     */
    public function __construct(?string $key = null)
    {
        if (!extension_loaded('openssl')) {
            throw new RuntimeException('OpenSSL extension is required for encryption.');
        }

        $this->cipher = Config::get('encryption.cipher', 'aes-256-gcm');
        $this->tagLength = Config::get('encryption.tag_length', 16);
        
        // Get IV length for the cipher
        $this->ivLength = openssl_cipher_iv_length($this->cipher);
        if ($this->ivLength === false) {
            throw new RuntimeException('Invalid cipher method.');
        }

        // Load key
        if ($key === null) {
            $key = Config::get('encryption.key', '');
            if (empty($key)) {
                throw new RuntimeException('Encryption key not configured.');
            }
        }

        // Validate key length
        $keyLength = strlen($key);
        if ($keyLength !== 32) {
            throw new RuntimeException('Encryption key must be 32 bytes for AES-256.');
        }

        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(string $plaintext): string
    {
        if (empty($plaintext)) {
            return '';
        }

        // Generate random IV
        $iv = random_bytes($this->ivLength);
        
        // Prepare additional authenticated data (AAD)
        $aad = '';
        
        // Encrypt with GCM
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            $aad,
            $this->tagLength
        );

        if ($ciphertext === false) {
            throw new RuntimeException('Encryption failed.');
        }

        // Combine: IV + Ciphertext + Tag
        $combined = $iv . $ciphertext . $tag;
        
        return base64_encode($combined);
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(string $ciphertext): string
    {
        if (empty($ciphertext)) {
            return '';
        }

        // Decode from base64
        $decoded = base64_decode($ciphertext, true);
        if ($decoded === false) {
            throw new RuntimeException('Invalid encrypted data format.');
        }

        // Extract components
        $ivLength = $this->ivLength;
        $tagLength = $this->tagLength;
        $totalLength = strlen($decoded);
        
        if ($totalLength < $ivLength + $tagLength) {
            throw new RuntimeException('Encrypted data too short.');
        }

        $iv = substr($decoded, 0, $ivLength);
        $tag = substr($decoded, -$tagLength);
        $ciphertextData = substr($decoded, $ivLength, $totalLength - $ivLength - $tagLength);

        // Decrypt
        $plaintext = openssl_decrypt(
            $ciphertextData,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            throw new RuntimeException('Decryption failed. Corrupted data or wrong key.');
        }

        return $plaintext;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function getCipher(): string
    {
        return $this->cipher;
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable(): bool
    {
        try {
            $testData = 'Test encryption';
            $encrypted = $this->encrypt($testData);
            $decrypted = $this->decrypt($encrypted);
            return $decrypted === $testData;
        } catch (RuntimeException $e) {
            return false;
        }
    }
}