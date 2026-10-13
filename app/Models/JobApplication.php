<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Database\Factories\JobApplicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company',
    'position',
    'location',
    'url',
    'contact_email',
    'status',
    'applied_on',
    'interview_at',
    'notes',
])]
class JobApplication extends Model
{
    /** @use HasFactory<JobApplicationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'applied_on' => 'date',
            'interview_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Keep a timeline of every status the application has been in.
        static::created(function (JobApplication $application) {
            $application->statusChanges()->create([
                'from_status' => null,
                'to_status' => $application->status,
            ]);
        });

        static::updated(function (JobApplication $application) {
            if ($application->wasChanged('status')) {
                $application->statusChanges()->create([
                    'from_status' => $application->getOriginal('status'),
                    'to_status' => $application->status,
                ]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusChanges(): HasMany
    {
        return $this->hasMany(StatusChange::class)->latest('id');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($term) {
            $query->where('company', 'like', "%{$term}%")
                ->orWhere('position', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%");
        });
    }

    public function scopeWithStatus(Builder $query, ?string $status): Builder
    {
        $status = ApplicationStatus::tryFrom((string) $status);

        return $status ? $query->where('status', $status) : $query;
    }
}
