<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Http\Requests\JobApplicationRequest;
use App\Models\JobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    private const SORTABLE = ['company', 'position', 'status', 'applied_on', 'created_at'];

    public function index(Request $request): View
    {
        $sort = in_array($request->query('sort'), self::SORTABLE, true) ? $request->query('sort') : 'created_at';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $applications = $request->user()->jobApplications()
            ->search($request->query('q'))
            ->withStatus($request->query('status'))
            ->orderBy($sort, $direction)
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('applications.index', [
            'applications' => $applications,
            'statuses' => ApplicationStatus::cases(),
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): View
    {
        return view('applications.create', [
            'application' => new JobApplication(['status' => ApplicationStatus::Applied, 'applied_on' => today()]),
        ]);
    }

    public function store(JobApplicationRequest $request): RedirectResponse
    {
        $application = $request->user()->jobApplications()->create($request->validated());

        return redirect()
            ->route('applications.show', $application)
            ->with('status', __('Application added.'));
    }

    public function show(JobApplication $application): View
    {
        Gate::authorize('view', $application);

        return view('applications.show', [
            'application' => $application->load('statusChanges'),
        ]);
    }

    public function edit(JobApplication $application): View
    {
        Gate::authorize('update', $application);

        return view('applications.edit', ['application' => $application]);
    }

    public function update(JobApplicationRequest $request, JobApplication $application): RedirectResponse
    {
        Gate::authorize('update', $application);

        $application->update($request->validated());

        return redirect()
            ->route('applications.show', $application)
            ->with('status', __('Application updated.'));
    }

    /**
     * Quick status change from the list or detail page.
     */
    public function updateStatus(Request $request, JobApplication $application): RedirectResponse
    {
        Gate::authorize('update', $application);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(ApplicationStatus::class)],
        ]);

        $application->update($validated);

        return back()->with('status', __('Status changed to :status.', [
            'status' => $application->status->label(),
        ]));
    }

    public function destroy(JobApplication $application): RedirectResponse
    {
        Gate::authorize('delete', $application);

        $application->delete();

        return redirect()
            ->route('applications.index')
            ->with('status', __('Application deleted.'));
    }
}
