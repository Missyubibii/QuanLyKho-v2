<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        // Lấy user hiện tại và đảm bảo kiểu để Intelephense nhận diện method hasPermission
        $user = Auth::user();

        // Nếu không đăng nhập hoặc user không phải instance của App\Models\User hoặc không có permission -> abort
        if (!Auth::check() || !($user instanceof User) || !$user->hasPermission($permission)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
