<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\StatusChange;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const WEEKS = 8;

    public function __invoke(Request $request): View
    {
        $applications = $request->user()->jobApplications()->get();

        $counts = [];
        foreach (ApplicationStatus::cases() as $status) {
            $counts[$status->value] = $applications->where('status', $status)->count();
        }

        $sent = $applications->where('status', '!==', ApplicationStatus::Wishlist);
        $responses = $sent->filter(fn ($application) => $application->status->isResponse());

        return view('dashboard', [
            'total' => $applications->count(),
            'counts' => $counts,
            'responseRate' => $sent->isEmpty() ? 0 : (int) round($responses->count() / $sent->count() * 100),
            'upcoming' => $applications
                ->filter(fn ($application) => $application->interview_at?->isFuture())
                ->sortBy('interview_at')
                ->take(5),
            'activity' => StatusChange::query()
                ->whereIn('job_application_id', $applications->pluck('id'))
                ->with('jobApplication')
                ->latest('id')
                ->take(6)
                ->get(),
            'weekly' => $this->weeklyCounts($sent),
        ]);
    }

    /**
     * Number of applications sent per week, oldest week first.
     *
     * @return array<int, array{label: string, count: int}>
     */
    private function weeklyCounts($sent): array
    {
        $weeks = [];
        $start = Carbon::now()->startOfWeek()->subWeeks(self::WEEKS - 1);

        for ($i = 0; $i < self::WEEKS; $i++) {
            $from = $start->copy()->addWeeks($i);
            $to = $from->copy()->endOfWeek();

            $weeks[] = [
                'label' => $from->translatedFormat('j M'),
                'count' => $sent->filter(fn ($application) => $application->applied_on?->between($from, $to))->count(),
            ];
        }

        return $weeks;
    }
}
