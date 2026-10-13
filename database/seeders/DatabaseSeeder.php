<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Fictional companies with the path each application took through the pipeline.
     * The number is how many days ago the application started.
     */
    private const DEMO = [
        ['Tulip Labs', 'Backend Developer Intern', 'Amsterdam', 52, ['applied', 'interview', 'offer']],
        ['Canal Digital', 'Junior Web Developer', 'Rotterdam', 47, ['applied', 'rejected']],
        ['Windmill Software', 'Software Developer Intern', 'Utrecht', 41, ['applied', 'interview']],
        ['Polder Cloud', 'DevOps Intern', 'Eindhoven', 38, ['applied', 'interview', 'rejected']],
        ['Stroopwafel Studio', 'Frontend Developer Intern', 'Den Haag', 33, ['applied']],
        ['Harbor Systems', 'Laravel Developer Intern', 'Rotterdam', 29, ['applied', 'interview']],
        ['Dune Analytics BV', 'Data Intern', 'Remote', 24, ['applied', 'rejected']],
        ['Bicycle Works', 'Full-Stack Intern', 'Amsterdam', 19, ['applied']],
        ['Orange Grid', 'IT Support Intern', 'Utrecht', 14, ['applied', 'interview']],
        ['North Sea Tech', 'Backend Developer Intern', 'Groningen', 9, ['applied']],
        ['Lighthouse Apps', 'Mobile Developer Intern', 'Remote', 4, ['applied']],
        ['Gouda Games', 'Unity Developer Intern', 'Amsterdam', 2, ['wishlist']],
        ['Delft Robotics', 'Software Engineer Intern', 'Delft', 1, ['wishlist']],
    ];

    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo Student',
            'email' => 'demo@applytrack.test',
        ]);

        foreach (self::DEMO as [$company, $position, $location, $daysAgo, $path]) {
            $this->seedApplication($user, $company, $position, $location, now()->subDays($daysAgo), $path);
        }
    }

    /**
     * @param  array<int, string>  $path
     */
    private function seedApplication(User $user, string $company, string $position, string $location, Carbon $start, array $path): void
    {
        $first = ApplicationStatus::from(array_shift($path));
        $final = ApplicationStatus::from(end($path) ?: $first->value);
        $slug = str($company)->slug();

        $application = $user->jobApplications()->create([
            'company' => $company,
            'position' => $position,
            'location' => $location,
            'url' => "https://careers.{$slug}.example/internship",
            'contact_email' => "hr@{$slug}.example",
            'status' => $first,
            'applied_on' => $first === ApplicationStatus::Wishlist ? null : $start,
            'interview_at' => $final === ApplicationStatus::Interview ? now()->addDays(rand(2, 20))->setTime(rand(9, 16), 0) : null,
            'notes' => $final === ApplicationStatus::Offer ? 'Offer received. Start date in February.' : null,
        ]);
        $application->statusChanges()->first()->update(['created_at' => $start]);
        $application->forceFill(['created_at' => $start])->saveQuietly();

        // Walk through the rest of the path so the timeline looks realistic.
        $moment = $start->copy();
        foreach ($path as $status) {
            $moment->addDays(rand(3, 9));
            $application->update(['status' => ApplicationStatus::from($status)]);
            $application->statusChanges()->first()->update(['created_at' => min($moment, now())]);
        }
    }
}
