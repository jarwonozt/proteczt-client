<?php

namespace Tecnozt\Proteczt\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tecnozt\Proteczt\ProtecztLicenseService;

class CheckProtecztLicense
{
    public function __construct(
        protected ProtecztLicenseService $license
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Always pass through for console commands
        if (app()->runningInConsole()) {
            return $next($request);
        }

        if (!$this->license->isActive()) {
            $status = $this->license->checkStatus();

            // Return JSON for API requests (Accept: application/json)
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application license is inactive or expired. Please contact your administrator.',
                    'code'    => 'LICENSE_INACTIVE',
                ], Response::HTTP_FORBIDDEN);
            }

            // Return HTML view for web requests
            return response()->view('proteczt::errors.license-expired', [
                'domain'   => $request->getHost(),
                'app_name' => config('app.name'),
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
