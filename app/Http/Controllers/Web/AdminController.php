<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Port;
use App\Models\Article;
use App\Models\RiskWeight;
use App\Models\Country;
use App\Models\NewsArticle;
use App\Models\RiskScore;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'users'     => User::count(),
            'countries' => Country::count(),
            'ports'     => Port::count(),
            'articles'  => Article::count(),
            'news'      => NewsArticle::count(),
            'risks'     => RiskScore::count(),
        ];

        $recentUsers = User::latest()->limit(5)->get();

        return view('admin.index', compact('stats', 'recentUsers'));
    }

    public function users()
    {
        $users = User::orderBy('name')->paginate(20);

        return view('admin.users', compact('users'));
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate(['role' => 'required|in:admin,user']);
        $user = User::findOrFail($id);
        $user->update(['role' => $request->role]);

        return back()->with('success', "Role for {$user->name} updated to {$request->role}.");
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'admin') {
            return back()->with('error', 'Cannot delete admin users.');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }

    public function ports()
    {
        $ports = Port::with('country')->orderBy('name')->paginate(30);

        return view('admin.ports', compact('ports'));
    }

    public function destroyPort($id)
    {
        Port::findOrFail($id)->delete();

        return back()->with('success', 'Port deleted successfully.');
    }

    public function articles()
    {
        $articles = Article::with('author')->latest('published_at')->paginate(20);

        return view('admin.articles', compact('articles'));
    }

    public function storeArticle(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'content'      => 'required|string',
            'published_at' => 'nullable|date',
        ]);

        $data['author_id']      = 1; // default admin
        $data['published_at'] ??= now();

        Article::create($data);

        return back()->with('success', 'Article created successfully.');
    }

    public function destroyArticle($id)
    {
        Article::findOrFail($id)->delete();

        return back()->with('success', 'Article deleted.');
    }

    public function riskWeights()
    {
        $weights = RiskWeight::all();

        return view('admin.risk-weights', compact('weights'));
    }

    public function updateRiskWeights(Request $request)
    {
        $data = $request->validate([
            'weights'   => 'required|array',
            'weights.*' => 'required|numeric|min:0|max:100',
        ]);

        $total = array_sum($data['weights']);

        if (abs($total - 100) > 0.01) {
            return back()->with('error', "Weights must sum to 100. Current sum: {$total}");
        }

        foreach ($data['weights'] as $component => $weight) {
            RiskWeight::where('component_name', $component)
                ->update(['weight_percentage' => $weight]);
        }

        return back()->with('success', 'Risk weights updated successfully.');
    }
}
