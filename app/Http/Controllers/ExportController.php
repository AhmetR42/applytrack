<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $applications = $request->user()->jobApplications()->orderByDesc('applied_on')->get();

        return response()->streamDownload(function () use ($applications) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Company', 'Position', 'Location', 'Status', 'Applied on', 'Interview at', 'URL', 'Contact', 'Notes']);

            foreach ($applications as $application) {
                fputcsv($out, [
                    $application->company,
                    $application->position,
                    $application->location,
                    $application->status->value,
                    $application->applied_on?->toDateString(),
                    $application->interview_at?->format('Y-m-d H:i'),
                    $application->url,
                    $application->contact_email,
                    $application->notes,
                ]);
            }

            fclose($out);
        }, 'applytrack-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
