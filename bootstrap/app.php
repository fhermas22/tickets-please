<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

use App\Traits\ApiResponses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Request;

$handlers = [
    ValidationException::class => 'handleValidationException',
    ModelNotFoundException::class => 'handleModelNotFoundException',
];

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/api_v1.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, Request $request) {

            $className = get_class($exception);

            if (array_key_exists($className, $this->handlers)) {
                $method = $this->handlers[$className];
                return response()->json($this->$method($exception));
            }

            if ($className == ValidationException::class) {
                foreach ($exception->errors() as $key => $value) {
                    foreach ($value as $message) {
                        $errors[] = [
                            'status' => 422,
                            'message' => $message,
                            'source' => $key,
                        ];
                    }
                }

                return response()->json([
                    [
                        $errors
                    ]
                ]);
            }

            $index = strrpos($className, '\\');

            return response()->json([
                [
                    'type' => substr($className, $index + 1),
                    'status' => 0,
                    'message' => $exception->getMessage(),
                    'source' => 'Line: ' . $exception->getLine() . ' : ' . $exception->getFile(),
                ]
            ]);
        });
    })->create();
