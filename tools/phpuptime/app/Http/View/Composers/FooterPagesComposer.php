<?php

namespace App\Http\View\Composers;

use App\Models\Page;
use Exception;
use Illuminate\Contracts\View\View;

class FooterPagesComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        try {
            $footerPages = Page::where('visibility', '<>', 0)->get();
        } catch (Exception) {
            $footerPages = [];
        }

        $view->with('footerPages', $footerPages);
    }
}
