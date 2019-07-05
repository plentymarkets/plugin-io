<?php

namespace IO\Services\ItemSearch\Factories\Faker\Traits;

trait FakeUnit
{
    protected function unit()
    {
        $units = [
            'C62',
            'KGM',
            'GRM',
            'MGM',
            'LTR',
            'DPC',
            'OP',
            'BL',
            'DI',
            'BG',
            'ST',
            'D64',
            'PD',
            'QR',
            'BX',
            'CL',
            'CH',
            'TN',
            'CA',
            'DZN',
            'BJ',
            'CS',
            'Z3',
            'BO',
            'OZA',
            'JR',
            'CG',
            'CT',
            'KT',
            'AA',
            'MTR',
            'MLT',
            'MMT',
            'PR',
            'PA',
            'PK',
            'D97',
            'MTK',
            'CMK',
            'MMK',
            'SCM',
            'SMM',
            'RO',
            'SA',
            'SET',
            'RL',
            'EA',
            'TU' ,
            'OZ',
            'WE',
            'CMT',
            'INH'
        ];

        $index = rand(0, count($units));
        return $units[$index];
    }
}

