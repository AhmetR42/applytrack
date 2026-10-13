<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobApplication>
 */
class JobApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(ApplicationStatus::cases());

        return [
            'user_id' => User::factory(),
            'company' => fake()->company(),
            'position' => fake()->randomElement([
                'Software Developer Intern',
                'Junior Web Developer',
                'Backend Developer Intern',
                'Frontend Developer Intern',
                'IT Support Intern',
                'DevOps Intern',
            ]),
            'location' => fake()->randomElement(['Amsterdam', 'Rotterdam', 'Utrecht', 'Eindhoven', 'Den Haag', 'Remote']),
            'url' => fake()->url(),
            'contact_email' => fake()->companyEmail(),
            'status' => $status,
            'applied_on' => $status === ApplicationStatus::Wishlist ? null : fake()->dateTimeBetween('-2 months'),
            'interview_at' => $status === ApplicationStatus::Interview ? fake()->dateTimeBetween('now', '+3 weeks') : null,
            'notes' => fake()->optional()->sentence(12),
        ];
    }

    public function status(ApplicationStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
