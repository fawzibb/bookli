<?php
namespace App\Services;

use Symfony\Component\HttpKernel\Exception\HttpException;

class PosLicenseSigner
{
    private function privateKey()
    {
        $path = config('pos.private_key_path');
        $key = is_readable($path) ? openssl_pkey_get_private(file_get_contents($path)) : false;
        if (!$key) throw new HttpException(503, 'POS licensing is not configured. Contact Bookli support.');
        $details = openssl_pkey_get_details($key);
        if ($details['type'] !== OPENSSL_KEYTYPE_RSA || $details['bits'] < 2048) throw new HttpException(503, 'POS signing key is invalid.');
        return $key;
    }
    public function assertReady(): void { $this->privateKey(); }
    private static function encode(string $value): string { return rtrim(strtr(base64_encode($value), '+/', '-_'), '='); }
    public function keyId(): string { return substr(hash('sha256', openssl_pkey_get_details($this->privateKey())['key']), 0, 16); }
    public function publicKey(): string { return openssl_pkey_get_details($this->privateKey())['key']; }
    public function sign(array $claims): string
    {
        $key = $this->privateKey();
        $header = self::encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT', 'kid' => $this->keyId()], JSON_THROW_ON_ERROR));
        $payload = self::encode(json_encode($claims, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        if (!openssl_sign($header.'.'.$payload, $signature, $key, OPENSSL_ALGO_SHA256)) throw new HttpException(503, 'Could not issue a POS license.');
        return $header.'.'.$payload.'.'.self::encode($signature);
    }
}
