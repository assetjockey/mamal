<?php

namespace App\Livewire\Admin\Accounts;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Services\Statistics\UserRegistration;
use App\Models\User;
use App\Models\Session;
use App\Models\GeneralSetting;
use App\Models\AdminKey;
use DB;

#[Title('Accounts Dashboard')]
class AccountsDashboard extends Component
{
    public array $chart_data = [];
    public array $user_countries = [];
    public $google_maps = false;
    public ?string $google_maps_key = null;

    public function mount()
    {
        $registrations = new UserRegistration();

        $this->chart_data['user_countries'] = json_encode($this->getAllCountries());
        $this->chart_data['current_year_registrations'] = json_encode($registrations->currentYearRegistrations());
        $this->chart_data['current_month_registrations'] = json_encode($registrations->currentMonthRegistrations());

        $this->user_countries = ['top_countries' => $this->getTopCountries()];
        $this->google_maps = (bool) GeneralSetting::first()?->google_maps;
        $this->google_maps_key = AdminKey::value('google_maps_api_key') ?: null;

    }
    
    public function render()
    {
        $onlineThreshold = now()->subMinutes(5)->timestamp;

        return view('livewire.admin.accounts.dashboard', [
            'totalUsers'         => User::count(),
            'totalCurrentUsers'  => User::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
            'onlineUsers'        => Session::where('last_activity', '>=', $onlineThreshold)->whereNotNull('user_id')->distinct('user_id')->count(),
            'visitorsToday'      => User::whereDate('last_seen', today())->count(),
            'chart_data'    => $this->chart_data,
            'user_countries'    => $this->user_countries,
        ]);
    }

    /**
     * Show list of all countries
     */
    public function getAllCountries()
    {
        $countries = User::select(DB::raw("count(id) as data, country"))
            ->groupBy('country')
            ->orderBy('data')
            ->pluck('data', 'country')
            ->mapWithKeys(function ($value, $key) {
                return [e($key) => $value];
            });

        return $countries;
    }

    /**
     * Show top 30 countries
     */
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
