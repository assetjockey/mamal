<?php

declare(strict_types=1);

namespace App\Extensions\AIAgentOutlook\System\Http\Controllers;

use App\Helpers\Classes\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AIAgentOutlookSettingController extends Controller
{
    public function index(Request $request): View
    {
        return view('ai-agent-outlook::admin.settings', [
            'clientId' => setting('microsoft_client_id', ''),
            'tenantId' => setting('microsoft_tenant_id', 'common'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (Helper::appIsDemo()) {
            return to_route('dashboard.user.index')->with([
                'status'  => 'error',
                'message' => trans('This feature is disabled in demo mode.'),
            ]);
        }

        $data = $request->validate([
            'microsoft_client_id'     => 'nullable|string',
            'microsoft_client_secret' => 'nullable|string',
            'microsoft_tenant_id'     => 'nullable|string',
        ]);

        // Don't overwrite secret if left blank
        if (blank($data['microsoft_client_secret'])) {
            unset($data['microsoft_client_secret']);
        }

        setting($data)->save();

        return back()->with([
            'type'    => 'success',
            'message' => trans('Settings updated successfully.'),
        ]);
    }
}
