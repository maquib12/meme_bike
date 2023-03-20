<?php

namespace App\Http\Middleware;
use Auth;
use Closure;

class LoginAuthenticate
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

        if (Auth::check()) {
            if((auth::user()->user_type_id == 1 )){
               return redirect()->route('dashboard');
            }
        }
        return $next($request);
    }
}
