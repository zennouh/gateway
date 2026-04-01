<?php


namespace App\Enum;

enum ServicesEnum: string
{
    case FORUMS = 'forums';
    case CHILDCARE = 'childcare';

    case JOBS = 'jobs';


    public const ALL = 'forums|childcare|jobs';

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
