<?php
namespace App\Exceptions;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PosLicenseException extends HttpException
{
    public function __construct(public readonly string $reason, string $message, int $status = 403) { parent::__construct($status, $message); }
    public function render(Request $request)
    {
        if ($request->is('api/*') || $request->expectsJson()) return response()->json(['error' => $this->reason, 'message' => $this->getMessage()], $this->getStatusCode());
        return null;
    }
}
