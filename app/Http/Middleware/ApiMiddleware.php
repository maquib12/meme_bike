<?php

namespace App\Http\Middleware;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use App\User;
use Exception;

use Closure;

class ApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth('api')->check()) {
             return $next($request);
        } else {
            $message = ["status"=>false,"message" => "Invalid Token","code"=>401];
            return response($message, 200);
        }
    }
}
