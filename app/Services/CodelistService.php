<?php

namespace App\Services;

class CodelistService
{
    /** @var array<array{code: string, name: string}> */
    public const GENDERS = [
        ['code' => 'm', 'name' => 'muž'],
        ['code' => 'f', 'name' => 'žena'],
    ];

    /** @var array<array{code: string, name: array{m: string, f: string, general: string}}> */
    public const MARITAL_STATUSES = [
        [
            'code' => 'single',
            'name' => [
                'm' => 'slobodný',
                'f' => 'slobodná',
                'general' => 'slobodný / slobodná',
            ],
        ],
        [
            'code' => 'married',
            'name' => [
                'm' => 'ženatý',
                'f' => 'vydatá',
                'general' => 'ženatý / vydatá',
            ],
        ],
        [
            'code' => 'divorced',
            'name' => [
                'm' => 'rozvedený',
                'f' => 'rozvedená',
                'general' => 'rozvedený / rozvedená',
            ],
        ],
        [
            'code' => 'widowed',
            'name' => [
                'm' => 'vdovec',
                'f' => 'vdova',
                'general' => 'vdovec / vdova',
            ],
        ],
    ];

    /** @var array<string> */
    public const TITLES_BEFORE = [
        'Bc.', 'Mgr.', 'Ing.', 'JUDr.', 'MVDr.', 'MUDr.', 'PaedDr.', 'prof.', 'doc.',
        'dipl.', 'MDDr.', 'Dr.', 'Mgr. art.', 'ThLic.', 'PhDr.', 'PhMr.', 'RNDr.',
        'ThDr.', 'RSDr.', 'arch.', 'PharmDr.',
    ];

    /** @var array<string> */
    public const TITLES_AFTER = [
        'CSc.', 'DrSc.', 'PhD.', 'ArtD.', 'DiS', 'DiS.art', 'FEBO', 'MPH', 'BSBA',
        'MBA', 'DBA', 'MHA', 'FCCA', 'MSc.', 'FEBU', 'LL.M',
    ];

    // -------------------
    // Public getters
    // -------------------

    /**
     * @return array<array{code: string, name: string}>
     */
    public static function getGenders(): array
    {
        return self::GENDERS;
    }

    /**
     * @return array<string>
     */
    public static function genderCodes(): array
    {
        return array_column(self::GENDERS, 'code');
    }

    /**
     * @return array<array{code: string, name: array{m: string, f: string, general: string}}>
     */
    public static function getMaritalStatuses(): array
    {
        return self::MARITAL_STATUSES;
    }

    /**
     * @return array<string>
     */
    public static function maritalStatusCodes(): array
    {
        return array_column(self::MARITAL_STATUSES, 'code');
    }

    /**
     * @return array<array{code: string, name: string}>
     */
    public static function getTitlesBefore(): array
    {
        return array_map(
            fn(string $title): array => ['code' => $title, 'name' => $title],
            self::TITLES_BEFORE
        );
    }

    /**
     * @return array<array{code: string, name: string}>
     */
    public static function getTitlesAfter(): array
    {
        return array_map(
            fn(string $title): array => ['code' => $title, 'name' => $title],
            self::TITLES_AFTER
        );
    }

    // -------------------
    // Validators
    // -------------------

    /**
     * @param array<string> $allowed
     */
    private static function isValid(?string $value, array $allowed): bool
    {
        return $value === null || in_array($value, $allowed, true);
    }

    public static function isValidGender(string $gender): bool
    {
        return self::isValid($gender, self::genderCodes());
    }

    public static function isValidMaritalStatus(?string $status): bool
    {
        return self::isValid($status, self::maritalStatusCodes());
    }

    public static function isValidTitleBefore(?string $title): bool
    {
        return self::isValid($title, self::TITLES_BEFORE);
    }

    public static function isValidTitleAfter(?string $title): bool
    {
        return self::isValid($title, self::TITLES_AFTER);
    }
}
