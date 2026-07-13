<?php

namespace App\Http\Middleware;

use Closure;

class SetLocale
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

        $headers = apache_request_headers();
       //dd($headers);
        if(isset($headers['Local']) && $headers['Local']=='ar' || isset($headers['local']) && $headers['local']=='ar') {
            $locale = 'ar';
            app()->setLocale($locale);
        }else{
            app()->setLocale('en');
        }

        return $next($request);
    }
}
