<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Country;
use App\Models\Port;
use App\Models\NewsArticle;
use App\Models\RiskScore;
use Illuminate\Http\Request;

class AdminApiController extends Controller
{
    public function users()
    {
        return response()->json(User::orderBy('name')->paginate(20));
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate(['role' => 'required|in:admin,user']);
        $user = User::findOrFail($id);
        $user->update(['role' => $request->role]);

        return response()->json(['message' => "Role updated to {$request->role}.", 'user' => $user]);
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return response()->json(['message' => 'Cannot delete admin users.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }

    public function stats()
    {
        return response()->json([
            'users'     => User::count(),
            'countries' => Country::count(),
            'ports'     => Port::count(),
            'news'      => NewsArticle::count(),
            'risks'     => RiskScore::count(),
            'admins'    => User::where('role', 'admin')->count(),
        ]);
    }
}
