<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveShow;
use App\Models\User;
use App\Models\Viewer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function home()
    {
        $activePlayers = User::role('user')->where('is_active', 1)->count();
        $rocOfPlayersFromLastWeek = User::role('user')
            ->where('is_active', 1)
            ->whereBetween('updated_at', [now()->subWeek(), now()])
            ->count();
        //calculate percentage
        if ($rocOfPlayersFromLastWeek == 0) {
            $rocOfPlayersFromLastWeekPercentage = $activePlayers * 100;
        } else {
            $rocOfPlayersFromLastWeekPercentage = (($activePlayers - $rocOfPlayersFromLastWeek) / $rocOfPlayersFromLastWeek) * 100;
        }

        $totalViewers = Viewer::count();
        $rocOfViewersFromLastWeek = Viewer::whereBetween('created_at', [now()->subWeek(), now()])->count();
        //calculate percentage
        if ($rocOfViewersFromLastWeek == 0) {
            $rocOfViewersFromLastWeekPercentage = $totalViewers * 100;
        } else {
            $rocOfViewersFromLastWeekPercentage = (($totalViewers - $rocOfViewersFromLastWeek) / $rocOfViewersFromLastWeek) * 100;
        }

        $totalLiveQuizShows = LiveShow::count();
        $totalScheduledLiveQuizShows = LiveShow::where('status', 'scheduled')->count();




        // Extract signups grouped by month for current year
        $year = Carbon::now()->year;

        $signups = User::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Create 12-month array (fill 0 for empty months)
        $monthlyUserData = array_fill(1, 12, 0);
        foreach ($signups as $row) {
            $monthlyUserData[$row->month] = $row->total;
        }

        $visits = Viewer::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Prepare 12-month array
        $monthlyViewerData = array_fill(1, 12, 0);
        foreach ($visits as $row) {
            $monthlyViewerData[$row->month] = $row->total;
        }


        $labels =  json_encode([
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        ]);
        $dataUsers = json_encode(array_values($monthlyUserData));
        $dataViewers = json_encode(array_values($monthlyViewerData));
        $year = $year;





        return view('admin.dashboard', compact('activePlayers', 'rocOfPlayersFromLastWeekPercentage', 'totalViewers', 'rocOfViewersFromLastWeekPercentage', 'totalLiveQuizShows', 'totalScheduledLiveQuizShows', 'labels', 'dataUsers', 'dataViewers', 'year'));
    }
}
