<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\AdCopy;
use App\Models\AdCreative;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Session;
use App\Models\Subscriber;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\GeneralSetting;
use App\Models\FinanceSetting;
use App\Models\AdminKey;
use App\Models\MediaModel;
use App\Models\TextModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;


#[Title('Admin Dashboard')]
class AdminDashboard extends Component
{
    public array $chart_data = [];
    public array $user_countries = [];
    public bool $google_maps = false;
    public bool $google_analytics_dashboard = false;
    public ?string $google_maps_key = null;

    public function mount()
    {
        $this->user_countries = ['top_countries' => $this->getTopCountries()];
        $this->google_maps = (bool) GeneralSetting::first()?->google_maps;
        // GA is "active" only when the admin enabled it AND the property id +
        // credentials file are actually configured (hydrated into config by
        // AppServiceProvider). This prevents the dashboard from firing the
        // analytics AJAX call when GA can't possibly succeed.
        $this->google_analytics_dashboard = (bool) GeneralSetting::first()?->google_analytics_dashboard
            && \App\Http\Controllers\Admin\AdminController::gaConfigured();
        $this->google_maps_key = config('services.google.maps.key')
            ?: (AdminKey::value('google_maps_api_key') ?: null);
    }

    public function render()
    {
        $today = $this->getTodayStats();
        $total = $this->getFinanceMetrics();
        $userMetrics = $this->getUserMetrics();
        $platformMetrics = $this->getPlatformMetrics();
        $revenueChartData = $this->getRevenueChartData();
        $monthlyFinance = $this->getMonthlyFinance();
        $revenueByPlanType = $this->getRevenueByPlanType();
        $userDistribution = $this->getUserDistribution();
        $creditsUsageChart = $this->getCreditsUsageChartData();
        $topModels = $this->getTopModels();
        $transactions = Order::select('id', 'plan_name', 'frequency', 'price', 'currency', 'gateway', 'status', 'created_at')->latest()->take(10)->get(); 
        $approvals = Order::with('user:id,name,email')
            ->select('id', 'user_id', 'plan_name', 'frequency', 'price', 'currency', 'gateway', 'status', 'created_at')
            ->where('status', 'pending')
            ->latest()
            ->get();
        $tickets = $this->getRecentTickets();
        $activities = $this->getRecentActivities();
        $topCountries = $this->user_countries['top_countries'] ?? collect();
        $currencySymbol = $this->getCurrencySymbol(FinanceSetting::first()?->currency ?? 'USD');

        return view('livewire.admin.dashboard.index', compact('today', 'total', 'userMetrics', 'platformMetrics', 'revenueChartData', 'monthlyFinance', 'revenueByPlanType', 'userDistribution', 'creditsUsageChart', 'topModels', 'transactions', 'approvals', 'tickets', 'activities', 'topCountries', 'currencySymbol'));
    }

    private function getCurrencySymbol(string $currency): string
    {
        $code = strtoupper($currency);
        $currencies = config('currencies', []);

        return $currencies[$code]['symbol'] ?? '$';
    }

    private function getTodayStats(): array
    {
        $todayStart = now()->startOfDay();

        return [
            'revenue' => Order::where('status', 'completed')
                ->whereDate('created_at', today())
                ->sum('price'),

            'new_users' => User::whereDate('created_at', today())->count(),

            'subscribers' => Subscriber::whereDate('created_at', today())
                ->where('status', 'active')
                ->count(),

            'transactions' => Order::whereDate('created_at', today())->count(),

            'tickets' => SupportTicket::whereDate('created_at', today())->count(),

            'online_users' => Session::whereNotNull('user_id')
                ->where('last_activity', '>=', $todayStart->timestamp)
                ->distinct('user_id')
                ->count('user_id'),

            // Credits spent today across both generation surfaces. Creatives are
            // charged on completion (use completed_at); copies are inserted as
            // completed at charge time (created_at is equivalent).
            'tokens_used' => (float) AdCreative::completed()
                    ->whereDate('completed_at', today())
                    ->sum('credits')
                + (float) AdCopy::completed()
                    ->whereDate('created_at', today())
                    ->sum('credits'),

            'media_used' => AdCreative::images()->completed()
                ->whereDate('completed_at', today())
                ->count(),

            'contents' => AdCreative::videos()->completed()
                ->whereDate('completed_at', today())
                ->count(),
        ];
    }

    private function getFinanceMetrics(): array
    {
        return [
            'total_income' => [['data' => Order::where('status', 'completed')->sum('price')]],
            'total_spending' => 0,
            'total_subscribers' => Subscriber::active()->count(),
            'referral_earnings' => [['data' => DB::table('referrals')->sum('commission')]],
            'referral_payouts' => [['data' => Payout::where('status', 'completed')->sum('total')]],
        ];
    }

    private function getUserMetrics(): array
    {
        return [
            'total_users' => User::count(),
            'total_subscribers' => Subscriber::active()->count(),
            'total_referred' => DB::table('referrals')->count(),
            'online_users' => Session::whereNotNull('user_id')
                ->where('last_activity', '>=', now()->subMinutes(5)->timestamp)
                ->distinct('user_id')
                ->count('user_id'),
            'visitors_today' => Session::whereNotNull('user_id')
                ->where('last_activity', '>=', now()->startOfDay()->timestamp)
                ->distinct('user_id')
                ->count('user_id'),
        ];
    }

    private function getPlatformMetrics(): array
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        $yearStart = now()->startOfYear();

        $currentYm = $currentMonth->format('Y-m');
        $lastYm = $lastMonth->format('Y-m');
        $yearYm = $yearStart->format('Y-m');

        // Widest boundary we need to scan back to. In January `lastMonth`
        // (December) precedes `yearStart`, otherwise `yearStart` is earlier.
        $rangeStart = $lastMonth->lt($yearStart) ? $lastMonth : $yearStart;

        $empty = fn () => ['current_month' => 0, 'last_month' => 0, 'yearly' => 0];

        // Which buckets a given 'Y-m' month string contributes to. Yearly uses a
        // string comparison, which is safe for the zero-padded 'Y-m' format.
        $periodsFor = fn (string $ym) => array_keys(array_filter([
            'current_month' => $ym === $currentYm,
            'last_month' => $ym === $lastYm,
            'yearly' => $ym >= $yearYm,
        ]));

        // Creatives are charged/finalized on completion, so we bucket them by
        // `completed_at`. One grouped query covers credits + per-type counts.
        $images = $empty();
        $videos = $empty();
        $creativeCredits = $empty();

        $creativeRows = AdCreative::completed()
            ->where('completed_at', '>=', $rangeStart)
            ->selectRaw("DATE_FORMAT(completed_at, '%Y-%m') as ym, type, COUNT(*) as cnt, SUM(credits) as credits")
            ->groupBy('ym', 'type')
            ->get();

        foreach ($creativeRows as $row) {
            foreach ($periodsFor($row->ym) as $period) {
                $creativeCredits[$period] += (float) $row->credits;
                if ($row->type === 'image') {
                    $images[$period] += (int) $row->cnt;
                } elseif ($row->type === 'video') {
                    $videos[$period] += (int) $row->cnt;
                }
            }
        }

        // Copies have no `completed_at` — they are inserted as `completed` at
        // charge time, so `created_at` is the equivalent moment. One grouped
        // query covers credits + words + count.
        $words = $empty();
        $copyCredits = $empty();
        $copiesCreated = $empty();

        $copyRows = AdCopy::completed()
            ->where('created_at', '>=', $rangeStart)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt, SUM(credits) as credits, SUM(words) as words")
            ->groupBy('ym')
            ->get();

        foreach ($copyRows as $row) {
            foreach ($periodsFor($row->ym) as $period) {
                $copyCredits[$period] += (float) $row->credits;
                $words[$period] += (int) $row->words;
                $copiesCreated[$period] += (int) $row->cnt;
            }
        }

        // Support tickets — one grouped query in place of three.
        $tickets = $empty();

        $ticketRows = SupportTicket::where('created_at', '>=', $rangeStart)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt")
            ->groupBy('ym')
            ->get();

        foreach ($ticketRows as $row) {
            foreach ($periodsFor($row->ym) as $period) {
                $tickets[$period] += (int) $row->cnt;
            }
        }

        // Credits actually consumed (creatives + copies draw from the same balance).
        $credits = [
            'current_month' => $creativeCredits['current_month'] + $copyCredits['current_month'],
            'last_month' => $creativeCredits['last_month'] + $copyCredits['last_month'],
            'yearly' => $creativeCredits['yearly'] + $copyCredits['yearly'],
        ];

        $pctChange = fn ($current, $last) => $last > 0
            ? round((($current - $last) / $last) * 100)
            : 0;

        $withChange = fn (array $m) => $m + ['change' => $pctChange($m['current_month'], $m['last_month'])];

        return [
            'credits' => $withChange($credits),
            'images' => $withChange($images),
            'videos' => $withChange($videos),
            'tickets' => $withChange($tickets),
            'words' => $withChange($words),
            'copies' => $withChange($copiesCreated),
        ];
    }

    private function getMonthlyFinance(): array
    {
        $now = now();
        $lastMonth = $now->copy()->subMonth();

        $earningThisMonth = Order::where('status', 'completed')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('price');

        $earningLastMonth = Order::where('status', 'completed')
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->sum('price');

        $spendingThisMonth = 0;
        $spendingLastMonth = 0;

        $pct = fn ($current, $last) => $last > 0
            ? round((($current - $last) / $last) * 100, 1)
            : ($current > 0 ? 100 : 0);

        return [
            'earning'         => $earningThisMonth,
            'earning_change'  => $pct($earningThisMonth, $earningLastMonth),
            'spending'        => $spendingThisMonth,
            'spending_change' => $pct($spendingThisMonth, $spendingLastMonth),
        ];
    }

    private function getRevenueChartData(): array
    {
        $months = collect(range(11, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());
        $start = $months->first();

        // One grouped query instead of 24 (12× sum + 12× count).
        $rows = Order::where('created_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN price ELSE 0 END) as revenue")
            ->selectRaw('COUNT(*) as orders')
            ->groupBy('ym')
            ->get()
            ->keyBy('ym');

        return [
            'labels'  => $months->map(fn ($m) => $m->format('M Y'))->values()->toArray(),
            'revenue' => $months->map(fn ($m) => (float) ($rows[$m->format('Y-m')]->revenue ?? 0))->values()->toArray(),
            'orders'  => $months->map(fn ($m) => (int) ($rows[$m->format('Y-m')]->orders ?? 0))->values()->toArray(),
        ];
    }

    private function getRevenueByPlanType(): array
    {
        return Order::where('orders.status', 'completed')
            ->join('plans', 'orders.plan_id', '=', 'plans.id')
            ->select('plans.name', DB::raw('sum(orders.price) as total'))
            ->groupBy('plans.name')
            ->orderByDesc('total')
            ->pluck('total', 'name')
            ->toArray();
    }

    /**
     * Credits consumed per month for the current year, split by surface:
     * media (image + video creatives) vs copy. Both draw from the same balance.
     * Powers the "Credits Usage" bar chart.
     */
    private function getCreditsUsageChartData(): array
    {
        $year = now()->year;

        $mediaRows = AdCreative::completed()
            ->whereYear('completed_at', $year)
            ->selectRaw('MONTH(completed_at) as m, SUM(credits) as credits')
            ->groupBy('m')
            ->pluck('credits', 'm')
            ->toArray();

        $copyRows = AdCopy::completed()
            ->whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as m, SUM(credits) as credits')
            ->groupBy('m')
            ->pluck('credits', 'm')
            ->toArray();

        $media = [];
        $copy = [];
        for ($m = 1; $m <= 12; $m++) {
            $media[] = round((float) ($mediaRows[$m] ?? 0), 2);
            $copy[] = round((float) ($copyRows[$m] ?? 0), 2);
        }

        return ['media' => $media, 'copy' => $copy];
    }

    /**
     * Most-used generation models, keyed by the actual `model_id` stored on
     * each generation row. Media models (creatives) resolve their labels via
     * `media_models`, text models (copies) via `text_models`. Returns a unified,
     * ranked list plus per-category splits for the "Top Used Models" chart.
     */
    private function getTopModels(): array
    {
        // Media — count completed creatives per model_id.
        $mediaCounts = AdCreative::completed()
            ->whereNotNull('model_id')
            ->selectRaw('model_id, COUNT(*) as cnt')
            ->groupBy('model_id')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'model_id')
            ->toArray();

        $mediaMeta = MediaModel::whereIn('model_id', array_keys($mediaCounts))
            ->get(['model_id', 'label', 'sub_label', 'type'])
            ->keyBy('model_id');

        // Text — count completed copies per model_id.
        $textCounts = AdCopy::completed()
            ->whereNotNull('model_id')
            ->selectRaw('model_id, COUNT(*) as cnt')
            ->groupBy('model_id')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'model_id')
            ->toArray();

        $textMeta = TextModel::whereIn('model_id', array_keys($textCounts))
            ->get(['model_id', 'label', 'vendor_label'])
            ->keyBy('model_id');

        $build = function (array $counts, $meta, string $kind): array {
            $out = [];
            foreach ($counts as $modelId => $count) {
                $row = $meta[$modelId] ?? null;
                $out[] = [
                    'model_id' => (string) $modelId,
                    'label'    => $row->label ?? ucfirst((string) $modelId),
                    'vendor'   => $kind === 'media'
                        ? ($row->sub_label ?? null)
                        : ($row->vendor_label ?? null),
                    'kind'     => $kind === 'media' ? ($row->type ?? 'media') : 'text',
                    'count'    => (int) $count,
                ];
            }
            return $out;
        };

        $media = $build($mediaCounts, $mediaMeta, 'media');
        $text = $build($textCounts, $textMeta, 'text');

        // Unified, ranked leaderboard across both surfaces.
        $all = array_merge($media, $text);
        usort($all, fn ($a, $b) => $b['count'] <=> $a['count']);
        $all = array_slice($all, 0, 8);

        $grandTotal = array_sum(array_column($all, 'count'));
        foreach ($all as &$row) {
            $row['percent'] = $grandTotal > 0
                ? round(($row['count'] / $grandTotal) * 100, 1)
                : 0;
        }
        unset($row);

        return [
            'all'   => $all,
            'media' => array_slice($media, 0, 6),
            'text'  => array_slice($text, 0, 6),
        ];
    }

    /**
     * Open / in-progress tickets for the dashboard support table.
     */
    private function getRecentTickets()
    {
        return SupportTicket::with('user:id,name,email')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->latest('updated_at')
            ->take(8)
            ->get();
    }

    /**
     * Latest admin notifications, shaped into a simple activity feed.
     */
    private function getRecentActivities()
    {
        $adminId = Auth::id();

        if (! $adminId) {
            return collect();
        }

        $admin = User::find($adminId);

        if (! $admin) {
            return collect();
        }

        return $admin->notifications()
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($notification) {
                $data = $notification->data ?? [];
                $title = $data['title'] ?? 'Notification';

                // Derive a coarse activity kind for icon/colour selection.
                $kind = match (true) {
                    str_contains(strtolower($title), 'payment') => 'payment',
                    str_contains(strtolower($title), 'register') => 'user',
                    str_contains(strtolower($title), 'payout') => 'payout',
                    default => 'general',
                };

                return [
                    'id'      => $notification->id,
                    'kind'    => $kind,
                    'title'   => $title,
                    'message' => $data['message'] ?? '',
                    'url'     => $data['url'] ?? null,
                    'time'    => $notification->created_at,
                ];
            });
    }

    private function getUserDistribution(): array
    {
        $map = [
            'user'    => 'Non Subscribers',
            'subscriber' => 'Subscribers',
        ];

        return User::select('group', DB::raw('count(*) as count'))
            ->groupBy('group')
            ->where('group', '<>', 'admin')
            ->pluck('count', 'group')
            ->mapWithKeys(fn ($count, $group) => [$map[$group] ?? $group => $count])
            ->toArray();
    }

    public function getTopCountries()
    {
        $countries = User::select(DB::raw("count(id) as data, country"))
            ->groupBy('country')
            ->orderByDesc('data')
            ->pluck('data', 'country')
            ->take(20)
            ->mapWithKeys(function ($value, $key) {
                return [e($key) => $value];
            });

        return $countries;
    }
}

