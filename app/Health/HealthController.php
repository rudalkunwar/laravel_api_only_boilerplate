<?php

declare(strict_types=1);

namespace App\Health;

use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

final class HealthController
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ];

        $healthy = collect($checks)->every(fn (array $check): bool => $check['healthy']);

        return ApiResponse::success(
            data: ['checks' => $checks],
            message: $healthy ? 'All systems operational.' : 'Some checks failed.',
            status: $healthy ? 200 : 503,
        );
    }

    /**
     * @return array{healthy: bool, message: string}
     */
    private function checkDatabase(): array
    {
        try {
            DB::select('SELECT 1');

            return ['healthy' => true, 'message' => 'Connected'];
        } catch (Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @return array{healthy: bool, message: string}
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_'.now()->timestamp;
            Cache::put($key, true, 10);
            $result = Cache::get($key);
            Cache::forget($key);

            return ['healthy' => $result === true, 'message' => $result === true ? 'Read/write ok' : 'Write failed'];
        } catch (Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }
}
