<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_user_sees_only_their_own_applications(): void
    {
        JobApplication::factory()->for($this->user)->create(['company' => 'Mine BV']);
        JobApplication::factory()->create(['company' => 'Someone Else BV']);

        $this->actingAs($this->user)
            ->get('/applications')
            ->assertOk()
            ->assertSee('Mine BV')
            ->assertDontSee('Someone Else BV');
    }

    public function test_user_can_create_an_application(): void
    {
        $response = $this->actingAs($this->user)->post('/applications', [
            'company' => 'Booking.com',
            'position' => 'Backend Developer Intern',
            'location' => 'Amsterdam',
            'status' => 'applied',
            'applied_on' => now()->toDateString(),
        ]);

        $application = JobApplication::first();
        $response->assertRedirect(route('applications.show', $application));

        $this->assertSame($this->user->id, $application->user_id);
        $this->assertSame(ApplicationStatus::Applied, $application->status);
        $this->assertCount(1, $application->statusChanges);
    }

    public function test_validation_rejects_bad_input(): void
    {
        $this->actingAs($this->user)
            ->post('/applications', [
                'company' => '',
                'position' => 'Intern',
                'status' => 'hired',
                'url' => 'not-a-url',
                'applied_on' => now()->addWeek()->toDateString(),
            ])
            ->assertSessionHasErrors(['company', 'status', 'url', 'applied_on']);

        $this->assertDatabaseCount('job_applications', 0);
    }

    public function test_user_can_update_an_application(): void
    {
        $application = JobApplication::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->put("/applications/{$application->id}", [
                'company' => 'Updated BV',
                'position' => $application->position,
                'status' => $application->status->value,
            ])
            ->assertRedirect(route('applications.show', $application));

        $this->assertSame('Updated BV', $application->fresh()->company);
    }

    public function test_status_change_is_recorded_in_timeline(): void
    {
        $application = JobApplication::factory()->for($this->user)
            ->status(ApplicationStatus::Applied)
            ->create();

        $this->actingAs($this->user)
            ->patch("/applications/{$application->id}/status", ['status' => 'interview'])
            ->assertRedirect();

        $latest = $application->fresh()->statusChanges()->first();

        $this->assertSame(ApplicationStatus::Applied, $latest->from_status);
        $this->assertSame(ApplicationStatus::Interview, $latest->to_status);
        $this->assertCount(2, $application->statusChanges);
    }

    public function test_user_can_delete_an_application(): void
    {
        $application = JobApplication::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->delete("/applications/{$application->id}")
            ->assertRedirect(route('applications.index'));

        $this->assertModelMissing($application);
    }

    public function test_user_cannot_touch_someone_elses_application(): void
    {
        $other = JobApplication::factory()->create();

        $this->actingAs($this->user)->get("/applications/{$other->id}")->assertForbidden();
        $this->actingAs($this->user)->get("/applications/{$other->id}/edit")->assertForbidden();
        $this->actingAs($this->user)->patch("/applications/{$other->id}/status", ['status' => 'offer'])->assertForbidden();
        $this->actingAs($this->user)->delete("/applications/{$other->id}")->assertForbidden();

        $this->assertModelExists($other);
    }

    public function test_list_can_be_searched_and_filtered(): void
    {
        JobApplication::factory()->for($this->user)->status(ApplicationStatus::Offer)->create(['company' => 'Adyen']);
        JobApplication::factory()->for($this->user)->status(ApplicationStatus::Rejected)->create(['company' => 'Bol']);

        $this->actingAs($this->user)
            ->get('/applications?q=ady')
            ->assertSee('Adyen')
            ->assertDontSee('>Bol<', false);

        $this->actingAs($this->user)
            ->get('/applications?status=rejected')
            ->assertSee('Bol')
            ->assertDontSee('Adyen');
    }

    public function test_dashboard_shows_statistics(): void
    {
        JobApplication::factory()->for($this->user)->status(ApplicationStatus::Applied)->create();
        JobApplication::factory()->for($this->user)->status(ApplicationStatus::Interview)->create();
        JobApplication::factory()->for($this->user)->status(ApplicationStatus::Wishlist)->create();

        $this->actingAs($this->user)
            ->get('/dashboard')
            ->assertOk()
            ->assertViewHas('total', 3)
            ->assertViewHas('responseRate', 50);
    }

    public function test_applications_can_be_exported_as_csv(): void
    {
        JobApplication::factory()->for($this->user)->create(['company' => 'Exact']);
        JobApplication::factory()->create(['company' => 'Hidden Corp']);

        $response = $this->actingAs($this->user)->get('/applications/export');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Exact', $csv);
        $this->assertStringNotContainsString('Hidden Corp', $csv);
    }
}
