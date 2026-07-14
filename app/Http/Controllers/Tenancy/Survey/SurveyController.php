<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy\Survey;

use App\Http\Controllers\Controller;
use App\Http\Requests\Survey\StoreOptionRequest;
use App\Http\Requests\Survey\StoreQuestionRequest;
use App\Http\Requests\Survey\StoreSurveyRequest;
use App\Models\Survey;
use App\Surveys\Exceptions\EntitlementDeniedException;
use App\Surveys\Exceptions\SurveyStateException;
use App\Surveys\Exceptions\SurveyValidationException;
use App\Surveys\SurveyEntitlements;
use App\Surveys\SurveyService;
use App\Surveys\SurveyVersionPublisher;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

/**
 * Tenant survey builder. Every action authorizes server-side via SurveyPolicy (never UI
 * hiding), delegates state changes to the survey services, and enforces survey entitlements
 * through the authoritative resolver (rule 32; Step 7 §14, §27, §23).
 */
final class SurveyController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly SurveyService $surveys,
        private readonly SurveyVersionPublisher $publisher,
        private readonly SurveyEntitlements $entitlements,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Survey::class);

        $surveys = Survey::query()->orderByDesc('id')->paginate(20);

        return view('surveys.index', compact('surveys'));
    }

    public function store(StoreSurveyRequest $request): RedirectResponse
    {
        $this->authorize('create', Survey::class);

        try {
            $this->entitlements->assertCanCreateSurvey(app(TenantContext::class)->tenant());
        } catch (EntitlementDeniedException $e) {
            return back()->withErrors(['entitlement' => $e->getMessage()]);
        }

        $survey = $this->surveys->create($request->validated(), $request->user());

        return redirect()->route('surveys.show', $survey)->with('status', __('Survey created.'));
    }

    public function show(Survey $survey): View
    {
        $this->authorize('view', $survey);

        $draft = $survey->versions()->where('status', 'draft')->orderByDesc('version_number')->with('questions.options')->first();
        $current = $survey->current_version_id !== null ? $survey->currentVersion()->with('questions.options')->first() : null;

        return view('surveys.show', compact('survey', 'draft', 'current'));
    }

    public function storeQuestion(StoreQuestionRequest $request, Survey $survey): RedirectResponse
    {
        $this->authorize('update', $survey);

        try {
            $draft = $this->surveys->draftVersion($survey);
            $this->surveys->addQuestion($draft, $request->validated(), $request->user());
        } catch (SurveyStateException $e) {
            return back()->withErrors(['question' => $e->getMessage()]);
        }

        return back()->with('status', __('Question added.'));
    }

    public function storeOption(StoreOptionRequest $request, Survey $survey, string $question): RedirectResponse
    {
        $this->authorize('update', $survey);

        $draft = $this->surveys->draftVersion($survey);
        $questionModel = $draft->questions()->where('ulid', $question)->firstOrFail();

        try {
            $this->surveys->addOption($questionModel, $request->validated(), $request->user());
        } catch (SurveyStateException $e) {
            return back()->withErrors(['option' => $e->getMessage()]);
        }

        return back()->with('status', __('Option added.'));
    }

    public function publish(Survey $survey): RedirectResponse
    {
        $this->authorize('publish', $survey);

        try {
            $draft = $this->surveys->draftVersion($survey);
            $this->publisher->publish($draft, request()->user());
        } catch (SurveyValidationException $e) {
            return back()->withErrors(['publish' => implode('; ', $e->errors)]);
        } catch (SurveyStateException $e) {
            return back()->withErrors(['publish' => $e->getMessage()]);
        }

        return redirect()->route('surveys.show', $survey)->with('status', __('Survey version published.'));
    }

    public function newVersion(Survey $survey): RedirectResponse
    {
        $this->authorize('update', $survey);

        $current = $survey->currentVersion()->firstOrFail();
        $this->publisher->newDraftFrom($current, request()->user());

        return redirect()->route('surveys.show', $survey)->with('status', __('New draft version created.'));
    }

    public function pause(Survey $survey): RedirectResponse
    {
        $this->authorize('pause', $survey);

        return $this->transition(fn () => $this->surveys->pause($survey, request()->user()), 'Survey paused.');
    }

    public function resume(Survey $survey): RedirectResponse
    {
        $this->authorize('pause', $survey);

        return $this->transition(fn () => $this->surveys->resume($survey, request()->user()), 'Survey resumed.');
    }

    public function archive(Survey $survey): RedirectResponse
    {
        $this->authorize('archive', $survey);

        return $this->transition(fn () => $this->surveys->archive($survey, request()->user()), 'Survey archived.');
    }

    private function transition(callable $action, string $message): RedirectResponse
    {
        try {
            $action();
        } catch (SurveyStateException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('status', __($message));
    }
}
