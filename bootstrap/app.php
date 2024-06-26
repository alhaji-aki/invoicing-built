<?php

use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: '',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('webhooks')
                ->name('webhooks.')
                ->group(base_path('routes/webhook.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(
            prepend: [ForceJsonResponse::class]
        );

        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (NotFoundHttpException $exception, $request) {
            if ($exception->getPrevious() instanceof ModelNotFoundException) {
                $modelName = Str::of($exception->getPrevious()->getModel())
                    ->afterLast('\\')
                    ->snake(' ')
                    ->title()
                    ->trim()
                    ->toString();

                return response()->json(['message' => "$modelName not found."], 404);
            }

            return response()->json([
                'message' => 'Resource is not available.',
            ], 404);
        });
    })->create();
