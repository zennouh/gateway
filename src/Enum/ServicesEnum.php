<?php


namespace App\Enum;

enum ServicesEnum: string
{
    case FORUMS = 'forums';
    case CHILDCARE = 'childcare';
    case JOBS = 'jobs';

    case ADMIN = 'admin';
    case MEDIA = 'media';


    public const ALL = 'forums|childcare|jobs|media|admin';

    public static function toString(): string
    {
        return implode(
            '|',
            array_map(
                fn($case) => $case->value,
                self::cases(),
            )
        );
    }
}
