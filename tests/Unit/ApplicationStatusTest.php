<?php

namespace Tests\Unit;

use App\Enums\ApplicationStatus;
use PHPUnit\Framework\TestCase;

class ApplicationStatusTest extends TestCase
{
    public function test_only_company_answers_count_as_a_response(): void
    {
        $this->assertFalse(ApplicationStatus::Wishlist->isResponse());
        $this->assertFalse(ApplicationStatus::Applied->isResponse());
        $this->assertTrue(ApplicationStatus::Interview->isResponse());
        $this->assertTrue(ApplicationStatus::Offer->isResponse());
        $this->assertTrue(ApplicationStatus::Rejected->isResponse());
    }

    public function test_statuses_are_in_pipeline_order(): void
    {
        $this->assertSame(
            ['wishlist', 'applied', 'interview', 'offer', 'rejected'],
            array_column(ApplicationStatus::cases(), 'value'),
        );
    }
}
