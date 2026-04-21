<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SidebarConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SidebarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $user->roles()->first()?->name;

        if (! $role) {
            return response()->json(['error' => 'No role assigned'], 403);
        }

        return response()->json([
            'role' => $role,
            'menu' => SidebarConfig::getMenuForRole($role),
        ]);
    }
}
