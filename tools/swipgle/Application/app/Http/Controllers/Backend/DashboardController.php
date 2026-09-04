<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BlogArticle;
use App\Models\Coupon;
use App\Models\Page;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEarnings = Transaction::where([['total_price', '!=', 0], ['status', 2]])->sum('total_price');
        $todayEarnings = Transaction::where([['total_price', '!=', 0], ['status', 2]])->whereDate('created_at', Carbon::today())->sum('total_price');
        $totalTransfers = Transfer::all()->count();
        $transactions = Transaction::where('status', 2)->orderbyDesc('id')->limit(6)->get();
        $totalUsers = User::all()->count();
        $totalTickets = SupportTicket::all()->count();
        $totalPages = Page::all()->count();
        $totalArticles = BlogArticle::all()->count();
        $totalTransactions = Transaction::whereIn('status', [2, 3])->count();
        $totalCoupons = Coupon::all()->count();
        $users = User::orderbyDesc('id')->limit(6)->get();
        return view('backend.dashboard.index', [
            'totalEarnings' => $totalEarnings,
            'todayEarnings' => $todayEarnings,
            'totalTransfers' => $totalTransfers,
            'transactions' => $transactions,
            'totalUsers' => $totalUsers,
            'totalTickets' => $totalTickets,
            'totalPages' => $totalPages,
            'totalArticles' => $totalArticles,
            'totalTransactions' => $totalTransactions,
            'totalCoupons' => $totalCoupons,
            'users' => $users,
        ]);
    }

    public function usersChartData()
    {
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();
        $dates = chartDates($startDate, $endDate);
        $usersRecord = User::where('created_at', '>=', Carbon::now()->startOfWeek())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');
        $usersRecordData = $dates->merge($usersRecord);
        $usersChartLabels = [];
        $usersChartData = [];
        foreach ($usersRecordData as $key => $value) {
            $usersChartLabels[] = Carbon::parse($key)->format('d F');
            $usersChartData[] = $value;
        }
        $suggestedMax = (max($usersChartData) > 10) ? max($usersChartData) + 2 : 10;
        return ['usersChartLabels' => $usersChartLabels, 'usersChartData' => $usersChartData, 'suggestedMax' => $suggestedMax];
    }

    public function earningsChartData()
    {
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();
        $dates = chartDates($startDate, $endDate);
        $getWeekEarnings = Transaction::where([['status', 2], ['created_at', '>=', Carbon::now()->startOfWeek()]])
            ->selectRaw('DATE(created_at) as date, SUM(total_price) as sum')
            ->groupBy('date')
            ->pluck('sum', 'date');
        $getEarningsData = $dates->merge($getWeekEarnings);
        $earningsChartLabels = [];
        $earningsChartData = [];
        foreach ($getEarningsData as $key => $value) {
            $earningsChartLabels[] = Carbon::parse($key)->format('d F');
            $earningsChartData[] = $value;
        }
        $suggestedMax = (max($earningsChartData) > 10) ? max($earningsChartData) + 2 : 10;
        return ['earningsChartLabels' => $earningsChartLabels, 'earningsChartData' => $earningsChartData, 'suggestedMax' => $suggestedMax];
    }

    public function transfersChartData()
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $dates = chartDates($startDate, $endDate);
        $monthlyTransfers = Transfer::where([['status', 1], ['created_at', '>=', Carbon::now()->startOfMonth()]])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');
        $monthlyTransfersData = $dates->merge($monthlyTransfers);
        $transfersChartLabels = [];
        $transfersChartData = [];
        foreach ($monthlyTransfersData as $key => $value) {
            $transfersChartLabels[] = Carbon::parse($key)->format('d F');
            $transfersChartData[] = $value;
        }
        $suggestedMax = (max($transfersChartData) > 10) ? max($transfersChartData) + 2 : 10;
        return ['transfersChartLabels' => $transfersChartLabels, 'transfersChartData' => $transfersChartData, 'suggestedMax' => $suggestedMax];
    }
}
