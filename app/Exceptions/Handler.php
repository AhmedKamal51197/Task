<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
                
        });
        $this->renderable(function (ModelNotFoundException $e, $request) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Resource not found',
            ], 404);
        });
        
        $this->renderable(function (NotFoundHttpException $e, $request) {
        
            return response()->json([
                'status' => 'failed',
                'message' => 'Not Found',
            ], 404);
        });
    }
    
}
