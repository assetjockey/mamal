<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Models\Incident;
use App\Models\Monitor;
use App\Models\User;
use App\Services\IncidentService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use League\Csv\Bom as CsvBom;
use League\Csv\Writer as CsvWriter;
use SplTempFileObject;

class IncidentController extends Controller
{
    /**
     * The incident service instance.
     */
    private IncidentService $incidentService;

    /**
     * Create a new controller instance.
     */
    public function __construct(IncidentService $incidentService)
    {
        $this->incidentService = $incidentService;
    }

    /**
     * List the incidents.
     */
    public function index(Request $request): View
    {
        $monitors = Monitor::where('user_id', $request->user()->id)->get();

        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['monitor', 'cause', 'comment']) ? $request->input('search_by') : 'monitor';
        $monitorId = $request->input('monitor_id');
        $status = $request->input('status');
        $sortBy = in_array($request->input('sort_by'), ['started_at', 'ended_at']) ? $request->input('sort_by') : 'ended_at';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        $incidents = Incident::with('monitor')
            ->where('user_id', $request->user()->id)
            ->when($monitorId, function ($query) use ($monitorId) {
                return $query->ofMonitor($monitorId);
            })
            ->when($status, function ($query) use ($status) {
                $query->ofStatus($status);
            })
            ->when($search, function ($query) use ($search, $searchBy) {
                if ($searchBy == 'monitor') {
                    return $query->whereHas('monitor', function ($query) use ($search) {
                        return $query->where('name', 'like', '%' . $search . '%');
                    });
                } elseif ($searchBy == 'comment') {
                    return $query->searchComment($search);
                } else {
                    return $query->searchCause($search);
                }
            })
            ->when($sortBy == 'ended_at', function ($query) use ($sort, $sortBy) {
                $query->orderByRaw('(`ended_at` IS NULL) ' . $sort);
            })
            ->orderBy($sortBy, $sort)
            ->paginate($perPage)
            ->appends(['search' => $search, 'search_by' => $searchBy, 'monitor_id' => $monitorId, 'status' => $status, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]);

        return view('incidents.index', ['incidents' => $incidents, 'monitors' => $monitors]);
    }

    /**
     * Show the create incident form.
     */
    public function create(Request $request): View
    {
        $monitors = Monitor::where([['user_id', '=', $request->user()->id]])->get();

        return view('incidents.new', ['monitors' => $monitors]);
    }

    /**
     * Show the incident.
     */
    public function show(Request $request, string $id): View
    {
        $incident = Incident::where([['id', $id]])->firstOrFail();

        if (Gate::denies('view', [$incident, $request->input('token')])) {
            abort(403);
        }

        $now = Carbon::now();

        return view('incidents.show', ['incident' => $incident, 'now' => $now]);
    }

    /**
     * Show the edit incident form.
     */
    public function edit(Request $request, string $id): View
    {
        $incident = Incident::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        return view('incidents.edit', ['incident' => $incident]);
    }

    /**
     * Store the incident.
     */
    public function store(StoreIncidentRequest $request): RedirectResponse
    {
        $incident = $this->incidentService->store($request->validated());

        return redirect()->route('incidents.show', $incident->id);
    }

    /**
     * Update the incident.
     */
    public function update(UpdateIncidentRequest $request, string $id): RedirectResponse
    {
        $incident = Incident::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $this->incidentService->update($incident, $request->validated());

        return back()->with('success', __('Settings saved.'));
    }

    /**
     * Acknowledge the incident.
     */
    public function acknowledge(Request $request, string $id): RedirectResponse
    {
        $incident = Incident::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $this->incidentService->update($incident, ['acknowledged_at' => Carbon::now()->tz($request->user()->timezone ?? config('settings.timezone'))]);

        return redirect()->route('incidents.show', $incident->id);
    }

    /**
     * Resolve the incident.
     */
    public function resolve(Request $request, string $id): RedirectResponse
    {
        $incident = Incident::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $this->incidentService->update($incident, ['ended_at' => Carbon::now()->tz($request->user()->timezone ?? config('settings.timezone'))]);

        return redirect()->route('incidents.show', $incident->id);
    }

    /**
     * Delete the incident.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        if ($request->has('bulk')) {
            Incident::where('user_id', '=', $request->user()->id)->whereIn('id', array_slice(json_decode($id, true), 0, 100))->each(function ($incident) use ($request) {
                $incident->delete();
            });

            return redirect()->route('incidents')->with('success', __(':count records have been deleted.', ['count' => $request->input('bulk')]));
        }

        $incident = Incident::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->firstOrFail();

        $incident->delete();

        return redirect()->route('incidents')->with('success', __(':name has been deleted.', ['name' => $incident->cause]));
    }

    /**
     * Export the incidents.
     */
    public function export(Request $request): Response
    {
        if ($request->user()->cannot('dataExport', [User::class])) {
            abort(403);
        }

        $now = Carbon::now();
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['monitor', 'cause', 'comment']) ? $request->input('search_by') : 'monitor';
        $monitorId = $request->input('monitor_id');
        $status = $request->input('status');
        $sortBy = in_array($request->input('sort_by'), ['started_at', 'ended_at']) ? $request->input('sort_by') : 'ended_at';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';

        $incidents = Incident::with('monitor')
            ->where('user_id', $request->user()->id)
            ->when($monitorId, function ($query) use ($monitorId) {
                return $query->ofMonitor($monitorId);
            })
            ->when($status, function ($query) use ($status) {
                $query->ofStatus($status);
            })
            ->when($search, function ($query) use ($search, $searchBy) {
                if ($searchBy == 'monitor') {
                    return $query->whereHas('monitor', function ($query) use ($search) {
                        return $query->where('name', 'like', '%' . $search . '%');
                    });
                } elseif ($searchBy == 'comment') {
                    return $query->searchComment($search);
                } else {
                    return $query->searchCause($search);
                }
            })
            ->when($sortBy == 'ended_at', function ($query) use ($sort, $sortBy) {
                $query->orderByRaw('(`ended_at` IS NULL) ' . $sort);
            })
            ->orderBy($sortBy, $sort)
            ->get();

        $content = CsvWriter::from(new SplTempFileObject);

        $content->insertOne([__('Type'), __('Incidents')]);
        $content->insertOne([__('Date'), $now->tz($request->user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) . ' ' . $now->tz($request->user()->timezone ?? config('settings.timezone'))->format('H:i:s') . ' (' . $now->tz($request->user()->timezone ?? config('settings.timezone'))->getOffsetString() . ')']);
        $content->insertOne([__('URL'), route('incidents', $request->query())]);
        $content->insertOne([__(' ')]);

        $content->insertOne([__('ID'), __('Monitor'), __('URL'), __('Cause'), __('Comment'), __('Duration') . ' (' . __('μs') . ')', __('Started at'), __('Ended at')]);
        foreach ($incidents as $incident) {
            $content->insertOne([$incident->id, $incident->monitor->name, $incident->url, $incident->cause, $incident->comment, $incident->started_at->diffInMicroseconds($incident->ended_at ?: $now), $incident->started_at->tz($request->user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')), ($incident->ended_at ? $incident->ended_at->tz($request->user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) : '')]);
        }

        $content->setOutputBOM(CsvBom::Utf8);

        return response((string) $content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition' => 'attachment; filename="' . formatTitle([__('Incidents'), config('settings.title')]) . '.csv"',
        ]);
    }
}
