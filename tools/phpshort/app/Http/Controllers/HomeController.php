<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLinkRequest;
use App\Models\Domain;
use App\Models\Link;
use App\Models\Plan;
use App\Services\LinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * The link service instance.
     */
    private LinkService $linkService;

    /**
     * Create a new controller instance.
     */
    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }

    /**
     * Show the home page.
     */
    public function index(Request $request): RedirectResponse|View
    {
        if (!config()->has('settings.title')) {
            return redirect()->route('install');
        }

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $localHost = parse_url(config('app.url'), PHP_URL_HOST);
        $remoteHost = $request->getHost();

        if ($localHost != $remoteHost) {
            $domain = Domain::where('name', '=', $remoteHost)->first();

            if ($domain) {
                if ($domain->homepage_url) {
                    return redirect()->to($domain->homepage_url, 301)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
                } else {
                    return redirect()->to(config('app.url'), 301)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
                }
            }
        }

        if (config('settings.homepage_redirect_url')) {
            return redirect()->to(config('settings.homepage_redirect_url'), 301)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }

        if (enabledPaymentProcessors()) {
            $user = Auth::user();

            $plans = Plan::where('visibility', 1)->orderBy('position')->orderBy('id')->get();

            $domains = Domain::select('name')->where('user_id', '=', 0)
                ->whereNotIn('id', [config('settings.short_domain_id')])
                ->get()
                ->map(function ($item) {
                    return $item->name;
                })
                ->toArray();
        } else {
            $user = null;
            $plans = null;
            $domains = null;
        }

        $defaultDomain = null;

        if (Domain::where([['user_id', '=', 0], ['id', '=', config('settings.short_domain_id')]])->exists()) {
            $defaultDomain = config('settings.short_domain_id');
        }

        $link = null;
        if ($request->session()->has('link_id')) {
            $link = Link::findOrFail($request->session()->get('link_id'));
        }

        return view('home.index', ['plans' => $plans, 'user' => $user, 'domains' => $domains, 'defaultDomain' => $defaultDomain, 'link' => $link]);
    }

    public function storeLink(StoreLinkRequest $request): RedirectResponse
    {
        if (!config('settings.short_guest')) {
            abort(404);
        }

        $link = $this->linkService->store($request->validated());

        return redirect()->back()->with('link_id', $link->id);
    }
}
