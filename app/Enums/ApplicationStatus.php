<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Wishlist = 'wishlist';
    case Applied = 'applied';
    case Interview = 'interview';
    case Offer = 'offer';
    case Rejected = 'rejected';

    public function label(): string
    {
        return __(ucfirst($this->value));
    }

    /**
     * Statuses that mean the company has answered the application.
     */
    public function isResponse(): bool
    {
        return in_array($this, [self::Interview, self::Offer, self::Rejected], true);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
