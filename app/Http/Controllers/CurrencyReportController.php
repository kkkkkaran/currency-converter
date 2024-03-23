<?php

namespace App\Http\Controllers;

use App\Http\Requests\CurrencyReportRequest;
use App\Http\Resources\ReportRequestResource;
use App\Models\ReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class CurrencyReportController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ReportRequestResource::collection(Auth::user()->reportRequests()->get());
    }

    public function store(CurrencyReportRequest $request): JsonResponse
    {
        ReportRequest::query()->create(array_merge($request->validated(), [
            'user_id' => Auth::user()->getAuthIdentifier(),
        ]));

        return response()->json([], 201);
    }
}
