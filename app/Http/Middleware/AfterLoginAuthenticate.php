<?php

namespace App\Http\Middleware;
use Auth;
use Closure;

class AfterLoginAuthenticate
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
        if(Auth::check())
       {
           if(auth()->user()->user_type_id == 1 ){
            $response=$next($request);
            $response->headers->set('Cache-Control','nocache,no-store,must-revalidate');
            return $response;
           }
       }else{
               return redirect()->route('login');
       }
       
    }
    
}
