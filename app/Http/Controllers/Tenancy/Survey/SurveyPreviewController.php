<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy\Survey;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Draft/version preview for authorized authors only. It renders the exact version but creates
 * no response, records no usage, and is never reachable from a public route (rule 32; Step 7
 * §15).
 */
final class SurveyPreviewController extends Controller
{
    use AuthorizesRequests;

    public function show(Survey $survey, string $version): View
    {
        $this->authorize('preview', $survey);

        $versionModel = $survey->versions()->where('ulid', $version)->with('questions.options')->firstOrFail();

        return view('surveys.preview', [
            'survey' => $survey,
            'version' => $versionModel,
        ]);
    }
}
