<?php

namespace App\Services\Statistics;

use App\Models\User;
use DB;

class UserRegistration
{
    private $year;
    private $month;

    public function __construct(?int $year = null, ?int $month = null)
    {
        $this->year = $year ?? (int) date('Y');
        $this->month = $month ?? (int) date('n');
    }

    public function currentMonthRegistrations()
    {
        $rows = User::select(DB::raw('count(id) as data'), DB::raw('DAY(created_at) as day'))
            ->whereYear('created_at', $this->year)
            ->whereMonth('created_at', $this->month)
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('data', 'day')
            ->map(fn($v) => (int) $v)
            ->toArray();

        return array_replace(array_fill(1, 31, 0), $rows);
    }

    public function currentYearRegistrations()
    {
        $rows = User::select(DB::raw('count(id) as data'), DB::raw('MONTH(created_at) as month'))
            ->whereYear('created_at', $this->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('data', 'month')
            ->map(fn($v) => (int) $v)
            ->toArray();

        return array_replace(array_fill(1, 12, 0), $rows);
    }
}