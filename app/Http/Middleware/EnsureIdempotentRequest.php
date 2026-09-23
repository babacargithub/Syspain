<?php

namespace App\Http\Middleware;

use App\Models\IdempotencyKey;
use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes a write request safe to send several times (double tap, keyboard "OK" + button, network retry).
 *
 * The client sends an "Idempotency-Key" header, unique per submission and reused when retrying it:
 *  - first request with the key: processed normally, its successful response is stored
 *  - same key while the first one is still running: 409, nothing is executed
 *  - same key after success: the stored response is returned, nothing is executed again
 *  - same key with a different payload: 422
 * Failed requests release their key so the user can correct the form and submit again.
 * Requests without the header are processed as before (old app versions keep working).
 */
class EnsureIdempotentRequest
{
    public const HEADER_NAME = 'Idempotency-Key';
    private const MAXIMUM_KEY_LENGTH = 100;

    public function handle(Request $request, Closure $next): Response
    {
        $idempotencyKey = $request->header(self::HEADER_NAME);
        if ($idempotencyKey === null || $request->isMethodSafe() || $request->user() === null) {
            return $next($request);
        }
        if (strlen($idempotencyKey) > self::MAXIMUM_KEY_LENGTH) {
            return response()->json(['message' => 'Clé d\'idempotence invalide'], Response::HTTP_BAD_REQUEST);
        }

        $requestFingerprint = hash('sha256', $request->method() . '|' . $request->path() . '|' . $request->getContent());

        try {
            $idempotencyRecord = IdempotencyKey::create([
                'user_id' => $request->user()->id,
                'idempotency_key' => $idempotencyKey,
                'request_fingerprint' => $requestFingerprint,
            ]);
        } catch (UniqueConstraintViolationException) {
            return $this->respondToRepeatedRequest($request->user()->id, $idempotencyKey, $requestFingerprint);
        }

        try {
            $response = $next($request);
        } catch (\Throwable $throwable) {
            $idempotencyRecord->delete();
            throw $throwable;
        }

        if ($response->isSuccessful()) {
            $idempotencyRecord->update([
                'response_status_code' => $response->getStatusCode(),
                'response_body' => $response->getContent(),
            ]);
        } else {
            $idempotencyRecord->delete();
        }

        return $response;
    }

    private function respondToRepeatedRequest(int $userIdentifier, string $idempotencyKey, string $requestFingerprint): Response
    {
        $existingRecord = IdempotencyKey::where('user_id', $userIdentifier)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        // the first request failed and released the key in the meantime: ask the client to retry
        if ($existingRecord === null) {
            return response()->json(['message' => 'Veuillez réessayer l\'opération'], Response::HTTP_CONFLICT);
        }
        if ($existingRecord->request_fingerprint !== $requestFingerprint) {
            return response()->json(
                ['message' => 'Cette clé d\'idempotence a déjà été utilisée pour une autre opération'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if ($existingRecord->isStillProcessing()) {
            return response()->json(
                ['message' => 'Cette opération est déjà en cours d\'enregistrement'],
                Response::HTTP_CONFLICT
            );
        }

        return response($existingRecord->response_body, $existingRecord->response_status_code)
            ->header('Content-Type', 'application/json')
            ->header('Idempotent-Replayed', 'true');
    }
}
