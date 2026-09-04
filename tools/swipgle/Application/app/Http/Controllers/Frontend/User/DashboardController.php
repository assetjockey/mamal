<?php

namespace App\Http\Controllers\Frontend\User;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function redirectToDashboard()
    {
        return redirect()->route('user.dashboard');
    }

    public function index(Request $request)
    {
        if ($request->input('search')) {
            $q = $request->input('search');
            $transfers = Transfer::where([['unique_id', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->OrWhere([['link', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->OrWhere([['sender_email', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->OrWhere([['sender_name', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->OrWhere([['emails', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->OrWhere([['subject', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->OrWhere([['message', 'like', '%' . $q . '%'], ['user_id', userAuthInfo()->id]])
                ->withCount('transferFiles')
                ->orderbyDesc('id')
                ->paginate(20);
            $transfers->appends(['q' => $q]);
        } else {
            $transfers = Transfer::where('user_id', userAuthInfo()->id)->withCount('transferFiles')->orderbyDesc('id')->paginate(20);
        }
        $totalTransfers = Transfer::where('user_id', userAuthInfo()->id)->count();
        return view('frontend.user.dashboard.index', ['transfers' => $transfers, 'totalTransfers' => $totalTransfers]);
    }
}
