<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Cashier\Billable;
use Illuminate\Support\Facades\Auth;

class Subscribed
{
    /**有料プランに登録済み
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $request->user()?->subscribed('premium_plan')) {
            return redirect('subscription/create');
        }
        return $next($request);
    }
}
