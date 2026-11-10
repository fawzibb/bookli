<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GeneratePosKeys extends Command
{
    protected $signature = 'pos:generate-keys';
    protected $description = 'Generate the server-only POS signing key pair without overwriting existing keys';
    public function handle(): int
    {
        $private = config('pos.private_key_path'); $public = config('pos.public_key_path');
        if (file_exists($private) || file_exists($public)) { $this->error('POS keys already exist. Existing keys were not changed.'); return self::FAILURE; }
        $key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 3072, 'config' => config('pos.openssl_config')]);
        if (!$key || !openssl_pkey_export($key, $pem, null, ['config' => config('pos.openssl_config')])) { $this->error('Could not generate POS keys.'); return self::FAILURE; }
        File::ensureDirectoryExists(dirname($private)); File::ensureDirectoryExists(dirname($public));
        $privateFile = @fopen($private, 'xb');
        if (!$privateFile) { $this->error('Could not create the private key. Existing files were not replaced.'); return self::FAILURE; }
        @chmod($private, 0600);
        $written = fwrite($privateFile, $pem); fclose($privateFile);
        if ($written !== strlen($pem)) { $this->error('Could not finish writing the private key. Check storage before retrying.'); return self::FAILURE; }
        $publicFile = @fopen($public, 'xb');
        if (!$publicFile) { $this->error('Private key created, but public key could not be created. Keep the private key and contact support.'); return self::FAILURE; }
        $publicPem = openssl_pkey_get_details($key)['key'];
        $written = fwrite($publicFile, $publicPem); fclose($publicFile);
        if ($written !== strlen($publicPem)) { $this->error('Public key write failed. Keep the private key and contact support.'); return self::FAILURE; }
        $this->info('POS signing keys generated. Keep the private key on the server and back it up securely.');
        return self::SUCCESS;
    }
}
