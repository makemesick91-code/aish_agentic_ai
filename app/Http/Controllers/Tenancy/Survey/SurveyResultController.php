<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy\Survey;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Surveys\SurveySummaryService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Read-only operational survey results. Permission- and branch-scoped; shows aggregate metrics
 * only, never individual answer content beyond the tenant's own scope (rule 32; Step 7 §24).
 */
final class SurveyResultController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly SurveySummaryService $summaries) {}

    public function show(Survey $survey): View
    {
        $this->authorize('viewResults', $survey);

        return view('surveys.results', [
            'survey' => $survey,
            'overview' => $this->summaries->overview($survey),
        ]);
    }
}
