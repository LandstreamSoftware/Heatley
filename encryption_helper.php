<?php

function getEncryptionKey(): string
{
    $base64Key = APP_SECRET_KEY ?? '';
    $key = base64_decode($base64Key, true);

    if ($key === false || strlen($key) !== 32) {
        throw new RuntimeException('APP_SECRET_KEY must be a valid base64-encoded 32-byte key');
    }

    return $key;
}

function encryptValue(string $plaintext): string
{
    $key = getEncryptionKey();
    $cipher = 'aes-256-gcm';

    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = random_bytes($ivLength);
    $tag = '';

    $ciphertext = openssl_encrypt(
        $plaintext,
        $cipher,
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($ciphertext === false) {
        throw new RuntimeException('Encryption failed');
    }

    return base64_encode($iv . $tag . $ciphertext);
}

function decryptValue(string $encoded): string
{
    $key = getEncryptionKey();
    $cipher = 'aes-256-gcm';

    $raw = base64_decode($encoded, true);
    if ($raw === false) {
        throw new RuntimeException('Encrypted value is not valid base64');
    }

    $ivLength = openssl_cipher_iv_length($cipher);
    $tagLength = 16;

    if (strlen($raw) < ($ivLength + $tagLength + 1)) {
        throw new RuntimeException('Encrypted payload is too short');
    }

    $iv = substr($raw, 0, $ivLength);
    $tag = substr($raw, $ivLength, $tagLength);
    $ciphertext = substr($raw, $ivLength + $tagLength);

    $plaintext = openssl_decrypt(
        $ciphertext,
        $cipher,
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($plaintext === false) {
        throw new RuntimeException('Decryption failed');
    }

    return $plaintext;
}