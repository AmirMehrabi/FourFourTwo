<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    public function index(): Response
    {
        $leaderboard = User::query()
            ->select('users.id', 'users.name', 'users.username', DB::raw('SUM(predictions.points_awarded) as total_points'))
            ->join('predictions', 'users.id', '=', 'predictions.user_id')
            ->whereNotNull('predictions.points_awarded')
            ->groupBy('users.id', 'users.name', 'users.username')
            ->orderBy('total_points', 'desc')
            ->orderBy('users.name', 'asc')
            ->get();

        return Inertia::render('Leaderboard/Index', [
            'leaderboard' => $leaderboard,
        ]);
    }
}
