<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class ApiAuthentication
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
        $api_token = Cache::tags('users')->get($request->name);
        if ($request->_token != $api_token){
            return response()->json([
                'message' => '权限验证错误'
            ]);
        }

        return $next($request);
    }
}
