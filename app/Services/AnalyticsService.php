<?php

namespace App\Services;

use App\Models\User;
use App\Models\PlayerProfile;
use App\Models\PlayerSituation;
use App\Models\PlayerMicroAction;
use App\Models\ActivityLog;
use App\Models\Situation;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getDashboard(): array
    {
        $totalUsers = User::count();
        $totalPlayers = PlayerProfile::count();
        $activePlayersToday = ActivityLog::where('created_at', '>=', today())
            ->whereIn('event_type', ['user.login', 'situation.completed', 'micro_action.performed'])
            ->distinct('user_id')
            ->count();

        $totalSituationsCompleted = PlayerSituation::whereNotNull('completed_at')->count();
        $totalMicroActionsPerformed = PlayerMicroAction::count();

        $avgStressLevel = PlayerProfile::avg('stress');
        $avgEnergyLevel = PlayerProfile::avg('energy');
        $avgPlayerLevel = PlayerProfile::avg('level');

        $topSituations = DB::table('player_situations')
            ->select('situations.title', DB::raw('COUNT(*) as completed_count'))
            ->join('situations', 'player_situations.situation_id', '=', 'situations.id')
            ->whereNotNull('player_situations.completed_at')
            ->groupBy('situations.id', 'situations.title')
            ->orderByDesc('completed_count')
            ->limit(5)
            ->get();

        $dailyStats = ActivityLog::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(DISTINCT user_id) as active_users'),
                DB::raw('COUNT(*) as total_events')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'success' => true,
            'data' => [
                'overview' => [
                    'total_users' => $totalUsers,
                    'total_players' => $totalPlayers,
                    'active_players_today' => $activePlayersToday,
                    'total_situations_completed' => $totalSituationsCompleted,
                    'total_micro_actions_performed' => $totalMicroActionsPerformed,
                ],
                'player_averages' => [
                    'stress_level' => round($avgStressLevel, 1),
                    'energy_level' => round($avgEnergyLevel, 1),
                    'player_level' => round($avgPlayerLevel, 1),
                ],
                'top_situations' => $topSituations,
                'daily_stats' => $dailyStats,
                'generated_at' => now(),
            ]
        ];
    }

    public function getPlayerBehavior(array $filters = []): array
    {
        $query = PlayerProfile::query();

        if (isset($filters['min_level'])) {
            $query->where('level', '>=', $filters['min_level']);
        }

        if (isset($filters['max_level'])) {
            $query->where('level', '<=', $filters['max_level']);
        }

        $players = $query->with(['user'])->get();

        $stressDistribution = [
            'low' => $players->where('stress', '<=', 20)->count(),
            'normal' => $players->whereBetween('stress', [21, 50])->count(),
            'elevated' => $players->whereBetween('stress', [51, 80])->count(),
            'high' => $players->where('stress', '>', 80)->count(),
        ];

        $levelDistribution = $players->groupBy('level')->map->count();

        $energyDistribution = [
            'critical' => $players->where('energy', '<', 50)->count(),
            'low' => $players->whereBetween('energy', [50, 99])->count(),
            'medium' => $players->whereBetween('energy', [100, 149])->count(),
            'high' => $players->where('energy', '>=', 150)->count(),
        ];

        $retentionStats = [
            'daily_login_streak_avg' => round($players->avg('consecutive_days'), 1),
            'last_login_today' => $players->where('last_login', '>=', today())->count(),
            'last_login_week' => $players->where('last_login', '>=', now()->subWeek())->count(),
            'last_login_month' => $players->where('last_login', '>=', now()->subMonth())->count(),
        ];

        return [
            'success' => true,
            'data' => [
                'total_players' => $players->count(),
                'stress_distribution' => $stressDistribution,
                'level_distribution' => $levelDistribution->toArray(),
                'energy_distribution' => $energyDistribution,
                'retention_stats' => $retentionStats,
                'filters_applied' => $filters,
            ]
        ];
    }

    public function getSituationStats(array $filters = []): array
    {
        $query = Situation::withCount([
            'playerSituations',
            'playerSituations as completed_count' => function ($query) {
                $query->whereNotNull('completed_at');
            }
        ]);

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['difficulty_level'])) {
            $query->where('difficulty_level', $filters['difficulty_level']);
        }

        $situations = $query->get();

        $categoryStats = DB::table('situations')
            ->select(
                'category',
                DB::raw('COUNT(*) as total_situations'),
                DB::raw('AVG(experience_reward) as avg_experience'),
                DB::raw('AVG(stress_impact) as avg_stress_impact')
            )
            ->groupBy('category')
            ->get();

        $difficultyStats = DB::table('situations')
            ->select(
                'difficulty_level',
                DB::raw('COUNT(*) as total_situations'),
                DB::raw('AVG(experience_reward) as avg_experience')
            )
            ->groupBy('difficulty_level')
            ->orderBy('difficulty_level')
            ->get();

        $optionChoiceStats = DB::table('player_situations')
            ->select(
                'situations.title as situation_title',
                'situation_options.text as option_text',
                DB::raw('COUNT(*) as choice_count')
            )
            ->join('situations', 'player_situations.situation_id', '=', 'situations.id')
            ->join('situation_options', 'player_situations.selected_option_id', '=', 'situation_options.id')
            ->whereNotNull('player_situations.completed_at')
            ->groupBy('situations.id', 'situations.title', 'situation_options.id', 'situation_options.text')
            ->orderByDesc('choice_count')
            ->limit(20)
            ->get();

        $completionRates = $situations->map(function ($situation) {
            $completionRate = $situation->player_situations_count > 0 
                ? ($situation->completed_count / $situation->player_situations_count) * 100 
                : 0;
                
            return [
                'id' => $situation->id,
                'title' => $situation->title,
                'category' => $situation->category,
                'difficulty_level' => $situation->difficulty_level,
                'total_attempts' => $situation->player_situations_count,
                'completed' => $situation->completed_count,
                'completion_rate' => round($completionRate, 1),
            ];
        });

        return [
            'success' => true,
            'data' => [
                'total_situations' => $situations->count(),
                'category_stats' => $categoryStats,
                'difficulty_stats' => $difficultyStats,
                'completion_rates' => $completionRates->sortByDesc('completion_rate')->values()->take(10),
                'popular_choices' => $optionChoiceStats,
                'filters_applied' => $filters,
            ]
        ];
    }

    public function getActivityStats(array $filters = []): array
    {
        $query = ActivityLog::query();

        if (isset($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $eventTypeStats = $query->select('event_type', DB::raw('COUNT(*) as count'))
            ->groupBy('event_type')
            ->orderByDesc('count')
            ->get();

        $hourlyStats = ActivityLog::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as activity_count')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $dailyUniqueUsers = ActivityLog::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'success' => true,
            'data' => [
                'event_type_stats' => $eventTypeStats,
                'hourly_activity' => $hourlyStats,
                'daily_unique_users' => $dailyUniqueUsers,
                'filters_applied' => $filters,
            ]
        ];
    }
}
