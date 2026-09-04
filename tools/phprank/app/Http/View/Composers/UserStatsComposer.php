<?php

namespace App\Http\View\Composers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class UserStatsComposer
{
    /**
     * @var
     */
    private $reportsCount;

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (!$this->reportsCount) {
                $this->reportsCount = $user->reportsCount;
            }

            $view->with('reportsCount', $this->reportsCount);
        }
    }
}
