<?php

namespace Tests\Unit;

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AuthControllerThrottleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_lockout_starts_after_five_failed_attempts_and_then_scales_every_two_attempts(): void
    {
        $controller = new class extends AuthController
        {
            public function keyFor(Request $request, string $usuario): string
            {
                return $this->getLoginThrottleKey($request, $usuario);
            }

            public function registerFailure(string $throttleKey): ?int
            {
                return $this->registerFailedLoginAttempt($throttleKey);
            }

            public function ensureNotLimited(string $throttleKey): void
            {
                $this->ensureLoginIsNotRateLimited($throttleKey);
            }
        };

        $request = Request::create('/login', 'POST', ['usuario' => 'demo']);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $throttleKey = $controller->keyFor($request, 'demo');

        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $this->assertNull($controller->registerFailure($throttleKey));
        }

        $this->assertSame(15, $controller->registerFailure($throttleKey));

        try {
            $controller->ensureNotLimited($throttleKey);
            $this->fail('El bloqueo de 15 segundos no se aplicó.');
        } catch (\Exception $exception) {
            $this->assertSame(13333, $exception->getCode());
            $this->assertStringContainsString('15 segundos', $exception->getMessage());
        }

        $this->travel(16)->seconds();
        $controller->ensureNotLimited($throttleKey);

        $this->assertNull($controller->registerFailure($throttleKey));
        $this->assertSame(30, $controller->registerFailure($throttleKey));
    }
}
