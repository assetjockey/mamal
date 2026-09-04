<?php

namespace App\Http\View\Composers;

use App\Traits\DateRangeTrait;
use App\Models\Website;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class UserWebsitesComposer
{
    use DateRangeTrait;

    /**
     * @var
     */
    private $websites;

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

            if (!$this->websites) {
                $this->websites = Website::where('user_id', $user->id)->whereNotNull('favorited_at')->orderBy('favorited_at', 'asc')->get();
            }

            $view->with('websites', $this->websites)->with('range', $this->range());
        }
    }
}
