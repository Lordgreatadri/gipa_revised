<?php

use App\Modules\Identity\Presentation\Http\Middleware\EnsureUserIsStaff;
use App\Modules\InvestorOnboarding\Presentation\Http\Middleware\EnsureInvestorHasBusinessProfile;
use App\Modules\InvestorOnboarding\Presentation\Http\Middleware\EnsureInvestorIsVerified;
use App\Modules\InvestorOnboarding\Presentation\Http\Middleware\EnsureUserIsInvestor;
use App\Modules\Shared\Application\RequestContext;
use App\Modules\Shared\Presentation\Http\Middleware\AuthenticateApiToken;
use App\Modules\Shared\Presentation\Http\Middleware\AssignRequestId;
use App\Modules\Shared\Presentation\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Spatie\WebhookClient\Exceptions\InvalidWebhookSignature;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Extra route files stay on the web stack. They are not wrapped
            // automatically the way routes/web.php is.
            Route::middleware('web')->group(base_path('routes/auth.php'));
            Route::middleware('web')->group(base_path('routes/investor.php'));
            Route::middleware('web')->group(base_path('routes/admin.php'));
            Route::middleware('throttle:sms-webhooks')->group(base_path('routes/webhook.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(AssignRequestId::class);
        $middleware->append(SecurityHeaders::class);

        $middleware->alias([
            'investor' => EnsureUserIsInvestor::class,
            'investor.verified' => EnsureInvestorIsVerified::class,
            'investor.profile' => EnsureInvestorHasBusinessProfile::class,
            'staff' => EnsureUserIsStaff::class,
            'auth.api' => AuthenticateApiToken::class,
        ]);

        // Unauthenticated API callers get JSON, never a redirect to the login page.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InvalidWebhookSignature $exception, Request $request) {
            if (! $request->is('webhooks/*')) {
                return null;
            }

            return response()->json(['message' => 'The callback was rejected.'], 403);
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        // ADR-0004: consistent, safe error envelope for /api/*.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $requestId = app(RequestContext::class)->requestId();

            if ($e instanceof ValidationException) {
                return response()->json([
                    'error' => [
                        'code' => 'validation_failed',
                        'message' => 'The request is invalid.',
                        'details' => $e->errors(),
                    ],
                    'meta' => ['request_id' => $requestId],
                ], $e->status);
            }

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();

                return response()->json([
                    'error' => [
                        'code' => match ($status) {
                            401 => 'unauthenticated',
                            403 => 'forbidden',
                            404 => 'not_found',
                            405 => 'method_not_allowed',
                            419 => 'token_mismatch',
                            429 => 'too_many_requests',
                            default => 'http_error',
                        },
                        'message' => $e->getMessage() !== '' ? $e->getMessage() : 'Request failed.',
                    ],
                    'meta' => ['request_id' => $requestId],
                ], $status, $e->getHeaders());
            }

            if (! config('app.debug')) {
                return response()->json([
                    'error' => [
                        'code' => 'server_error',
                        'message' => 'An unexpected error occurred.',
                    ],
                    'meta' => ['request_id' => $requestId],
                ], 500);
            }

            return null;
        });
    })->create();
