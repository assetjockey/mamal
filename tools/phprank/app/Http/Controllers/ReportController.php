<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Http\Requests\ValidateReportPasswordRequest;
use App\Models\Report;
use App\Traits\ReportTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\Csv as CSV;

class ReportController extends Controller
{
    use ReportTrait;

    /**
     * List the Reports.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        // If there's no toast notification
        if ($request->session()->get('toast') == false) {
            // Set the session to a countable object
            $request->session()->put('toast', []);
        }

        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['url']) ? $request->input('search_by') : 'url';
        $project = $request->input('project');
        $score = $request->input('score');
        $sortBy = in_array($request->input('sort_by'), ['id', 'generated_at', 'url', 'score']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $reports = Report::where('user_id', $request->user()->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->searchUrl($search);
            })
            ->when($project, function ($query) use ($project) {
                return $query->ofProject($project);
            })
            ->when($score, function ($query) use ($score) {
                return $query->ofScore($score);
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'project' => $project, 'score' => $score, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        $projects = Report::select('project')->where('user_id', $request->user()->id)->groupBy('project')->orderBy('project', 'asc')->get(['project']);

        return view('reports.list', ['reports' => $reports, 'projects' => $projects]);
    }

    /**
     * Show the Report.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Request $request, $id)
    {
        $report = Report::where('id', '=', $id)->firstOrFail();

        if ($this->guard($report)) {
            return view('reports.password', ['report' => $report]);
        }

        $now = Carbon::now();

        return view('reports.container', ['view' => 'show', 'report' => $report, 'now' => $now]);
    }

    /**
     * Show the edit Report form.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, $id)
    {
        $report = Report::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        return view('reports.container', ['view' => 'edit', 'report' => $report]);
    }

    /**
     * Store the Report.
     *
     * @param StoreReportRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function store(StoreReportRequest $request)
    {
        if ($request->input('sitemap') && config('settings.sitemap_links') != 0) {
            $reports = $this->reportsStore($request);

            return redirect()->back()->with('toast', Report::where('user_id', '=', $request->user()->id)->orderBy('id', 'desc')->limit(count($reports))->get());
        } else {
            try {
                $this->reportStore($request);
            } catch (\Exception $e) {
                return back()->with('error', __('An unexpected error has occurred, please try again.') . $e->getMessage())->withInput();
            }

            return redirect()->back()->with('toast', Report::where('user_id', '=', $request->user()->id)->orderBy('id', 'desc')->limit(1)->get());
        }
    }

    /**
     * Update the Report.
     *
     * @param UpdateReportRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(UpdateReportRequest $request, $id)
    {
        $report = Report::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        try {
            $this->reportUpdate($request, $report);
        } catch (\Exception $e) {
            return back()->with('error', __('An unexpected error has occurred, please try again.') . $e->getMessage())->withInput();
        }

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Delete the Report.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(Request $request, $id)
    {
        if ($request->has('bulk')) {
            Report::where('user_id', '=', $request->user()->id)->whereIn('id', array_slice(json_decode($id, true), 0, 100))->each(function ($report) use ($request) {
                $report->delete();
            });

            return redirect()->route('reports')->with('success', __(':count records have been deleted.', ['count' => $request->input('bulk')]));
        }

        $report = Report::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $report->delete();

        return redirect()->route('reports', ['project' => request()->input('project')])->with('success', __(':name has been deleted.', ['name' => $report->url]));
    }

    /**
     * Export the Reports.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws CSV\CannotInsertRecord
     */
    public function export(Request $request)
    {
        if ($request->user()->cannot('dataExport', ['App\Models\User'])) {
            abort(403);
        }

        $now = Carbon::now();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['url']) ? $request->input('search_by') : 'url';
        $project = $request->input('project');
        $score = $request->input('score');
        $sortBy = in_array($request->input('sort_by'), ['id', 'generated_at', 'url', 'score']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        $reports = Report::where('user_id', $request->user()->id)
            ->when($search, function ($query) use ($search, $searchBy) {
                return $query->searchUrl($search);
            })
            ->when($project, function ($query) use ($project) {
                return $query->ofProject($project);
            })
            ->when($score, function ($query) use ($score) {
                return $query->ofScore($score);
            })
            ->orderBy($sortBy, $sort)
            ->get();

        $content = CSV\Writer::createFromFileObject(new \SplTempFileObject);

        // Generate the header
        $content->insertOne([__('Type'), __('Reports')]);
        $content->insertOne([__('Date'), $now->tz($request->user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) . ' ' . $now->tz($request->user()->timezone ?? config('settings.timezone'))->format('H:i:s') . ' (' . $now->tz($request->user()->timezone ?? config('settings.timezone'))->getOffsetString() . ')']);
        $content->insertOne([__('URL'), $request->fullUrl()]);
        $content->insertOne([__(' ')]);

        // Generate the content
        $content->insertOne([__('ID'), __('URL'), __('Score'), __('High issues'), __('Medium issues'), __('Low issues'), __('Generated at'), __('Created at')]);
        foreach ($reports as $report) {
            $content->insertOne([$report->id, $report->url, $report->score, $report->highIssuesCount, $report->mediumIssuesCount, $report->lowIssuesCount, $report->generated_at->tz($request->user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')), $report->created_at->tz($request->user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d'))]);
        }

        // Set the output BOM
        $content->setOutputBOM(CSV\Reader::BOM_UTF8);

        return response((string) $content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition' => 'attachment; filename="' . formatTitle(($project ? [e($request->input('project')), __('Reports'), config('settings.title')] : [__('Reports'), config('settings.title')])) . '.csv"',
        ]);
    }

    /**
     * Validate the Model's password.
     *
     * @param ValidateReportPasswordRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function validatePassword(ValidateReportPasswordRequest $request, $id)
    {
        session([md5($id) => true]);
        return redirect()->back();
    }

    /**
     * Guard the Model.
     *
     * @param $model
     * @return bool
     */
    private function guard($model)
    {
        // If the model is not set to public
        if($model->privacy !== 0) {
            $user = Auth::user();

            // If the model's privacy is set to private
            if ($model->privacy == 1) {
                // If the user is not authenticated
                // Or if the user is not the owner of the model and not an admin
                if ($user == null || $user->id != $model->user_id && $user->role != 1) {
                    abort(403);
                }
            }

            // If the model's privacy is set to password
            if ($model->privacy == 2) {
                // If there's no password validation in the current session
                if (!session(md5($model->id))) {
                    // If the user is not authenticated
                    // Or if the user is not the owner of the link and not an admin
                    if ($user == null || $user->id != $model->user_id && $user->role != 1) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
