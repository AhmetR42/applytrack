<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleTest extends TestCase
{
    public function test_english_is_the_default(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Track your internship applications');
    }

    public function test_user_can_switch_to_dutch_and_back(): void
    {
        $this->from('/')->post('/locale', ['locale' => 'nl'])->assertRedirect('/');

        $this->get('/')
            ->assertSee('<html lang="nl">', false)
            ->assertSee('Houd je stagesollicitaties bij');

        $this->post('/locale', ['locale' => 'en']);

        $this->get('/')->assertSee('<html lang="en">', false);
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $this->post('/locale', ['locale' => 'de'])->assertSessionHasErrors('locale');

        $this->get('/')->assertSee('<html lang="en">', false);
    }

    /**
     * Every string passed to __() or trans_choice() must have a Dutch translation.
     */
    public function test_every_interface_string_has_a_dutch_translation(): void
    {
        $dutch = json_decode(file_get_contents(lang_path('nl.json')), true);
        $missing = [];

        $files = array_merge(
            glob(resource_path('views/**/*.blade.php')),
            glob(resource_path('views/*.blade.php')),
            glob(app_path('{Enums,Http/Controllers,Http/Controllers/Auth}/*.php'), GLOB_BRACE),
        );

        foreach ($files as $file) {
            preg_match_all("/(?:__|trans_choice)\\('([^']+)'/", file_get_contents($file), $matches);

            foreach ($matches[1] as $key) {
                if (! array_key_exists($key, $dutch)) {
                    $missing[] = $key;
                }
            }
        }

        foreach (['Wishlist', 'Applied', 'Interview', 'Offer', 'Rejected'] as $status) {
            if (! array_key_exists($status, $dutch)) {
                $missing[] = $status;
            }
        }

        $this->assertSame([], array_values(array_unique($missing)), 'Missing Dutch translations');
    }
}
