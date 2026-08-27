<?php
/**
 * Encryption Interface
 * 
 * Defines the contract for encryption services.
 */

namespace App\Interfaces;

interface EncryptionInterface
{
    /**
     * Encrypt data
     * 
     * @param string $plaintext Plain text to encrypt
     * @return string Encrypted ciphertext (base64 encoded with IV and tag)
     */
    public function encrypt(string $plaintext): string;

    /**
     * Decrypt data
     * 
     * @param string $ciphertext Encrypted data (base64 encoded)
     * @return string Decrypted plaintext
     * @throws \RuntimeException If decryption fails
     */
    public function decrypt(string $ciphertext): string;

    /**
     * Get the encryption key
     * 
     * @return string The encryption key (binary)
     */
    public function getKey(): string;

    /**
     * Get the cipher method
     * 
     * @return string
     */
    public function getCipher(): string;

    /**
     * Check if encryption is available
     * 
     * @return bool
     */
    public function isAvailable(): bool;
}