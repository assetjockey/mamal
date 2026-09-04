<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\StoreReportRequest;
use App\Http\Requests\API\UpdateReportRequest;
use App\Http\Resources\ReportResource;
use App\Traits\ReportTrait;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ReportTrait;

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $searchBy = in_array($request->input('search_by'), ['url']) ? $request->input('search_by') : 'url';
        $project = $request->input('project');
        $score = $request->input('score');
        $sortBy = in_array($request->input('sort_by'), ['id', 'url', 'score']) ? $request->input('sort_by') : 'id';
        $sort = in_array($request->input('sort'), ['asc', 'desc']) ? $request->input('sort') : 'desc';
        $perPage = in_array($request->input('per_page'), [10, 25, 50, 100]) ? $request->input('per_page') : config('settings.paginate');

        return ReportResource::collection(Report::where('user_id', $request->user()->id)
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
            ->appends(['search' => $search, 'search_by' => $searchBy, 'project' => $project, 'score' => $score, 'sort_by' => $sortBy, 'sort' => $sort, 'per_page' => $perPage]))
            ->additional(['status' => 200]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreReportRequest $request
     * @return ReportResource|\Illuminate\Http\JsonResponse
     */
    public function store(StoreReportRequest $request)
    {
        try {
            $created = $this->reportStore($request);

            if ($created) {
                return ReportResource::make($created);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => __('An unexpected error has occurred, please try again.') . $e->getMessage(),
                'status' => 500
            ], 500);
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param $id
     * @return ReportResource|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $link = Report::where([['id', '=', $id], ['user_id', $request->user()->id]])->first();

        if ($link) {
            return ReportResource::make($link);
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateReportRequest $request
     * @param $id
     * @return ReportResource|\Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(UpdateReportRequest $request, $id)
    {
        $report = Report::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($report) {
            try {
                $updated = $this->reportUpdate($request, $report);

                if ($updated) {
                    return ReportResource::make($updated);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'message' => __('An unexpected error has occurred, please try again.') . $e->getMessage(),
                    'status' => 500
                ], 500);
            }
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy(Request $request, $id)
    {
        $report = Report::where([['id', '=', $id], ['user_id', '=', $request->user()->id]])->first();

        if ($report) {
            $report->delete();

            return response()->json([
                'id' => $report->id,
                'object' => 'report',
                'deleted' => true,
                'status' => 200
            ], 200);
        }

        return response()->json([
            'message' => __('Resource not found.'),
            'status' => 404
        ], 404);
    }
}
