<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserType
{
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  mixed ...$types   // accepts multiple types: Ogm,Op,Os, Ot
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$types)
    {
        $user = $request->user();

        // Option 1: By model class (if you use separate models)
        foreach ($types as $type) {
            $expectedClass = '\\App\\Models\\' . ucfirst($type);
            if ($user instanceof $expectedClass) {
                return $next($request);
            }
        }

        // Option 2: By attribute (if you have a 'type' column on a shared User model)
        // if (in_array($user->type ?? null, $types)) return $next($request);

        return response()->json(['message' => 'Unauthorized'], 403);
    }

}
