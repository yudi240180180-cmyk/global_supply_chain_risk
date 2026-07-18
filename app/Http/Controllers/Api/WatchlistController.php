<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Watchlist;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $watchlist = Watchlist::with('country')
            ->where('user_id', $user->id)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'country_id' => $item->country_id,
                'country_name' => $item->country?->name,
                'country_code' => $item->country?->code,
            ]);

        return response()->json(['watchlist' => $watchlist]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'country_id' => 'required|exists:countries,id',
        ]);

        $watchlist = Watchlist::firstOrNew([
            'user_id' => $user->id,
            'country_id' => $data['country_id'],
        ]);

        $status = $watchlist->exists ? 200 : 201;

        if (! $watchlist->exists) {
            $watchlist->save();
        }

        $watchlist->load('country');

        return response()->json([
            'watchlist' => [
                'id' => $watchlist->id,
                'user_id' => $watchlist->user_id,
                'country_id' => $watchlist->country_id,
                'country_name' => $watchlist->country?->name,
                'country_code' => $watchlist->country?->code,
            ],
        ], $status);
    }

    public function destroy($id)
    {
        $user = request()->user();

        $watchlist = Watchlist::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $watchlist) {
            return response()->json(['message' => 'Watchlist item not found'], 404);
        }

        $watchlist->delete();

        return response()->json(['message' => 'Watchlist item deleted']);
    }
}
