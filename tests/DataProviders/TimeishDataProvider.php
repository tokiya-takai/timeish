<?php

declare(strict_types=1);

namespace Tests\DataProviders;

class TimeishDataProvider
{
    public function validData()
    {
        return [
            [0, 0],
            [0, 59],
            [99, 0],
            [99, 59],
            [100, 0],
            [10000, 0],
        ];
    }

    public function invalidData()
    {
        return [
            [-1, 0],
            [0, -1],
            [-100, 0],
            [0, -100],
            [0, 60],
        ];
    }

    public function invalidTypeData()
    {
        return [
            ['a', 0],
            [0, 'a'],
        ];
    }

    public function separators()
    {
        return [
            [':'],
            ['.'],
            ['-'],
        ];
    }
}
