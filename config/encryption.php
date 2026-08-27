<?php
/**
 * Encryption Configuration
 * 
 * Returns encryption settings from environment variables.
 */

return [
    'cipher' => 'aes-256-gcm',
    'key' => hex2bin(getenv('ENCRYPTION_KEY') ?: ''),
    'key_derivation_iterations' => 100000,
    'tag_length' => 16, // GCM tag length in bytes
    'iv_length' => openssl_cipher_iv_length('aes-256-gcm'),
];