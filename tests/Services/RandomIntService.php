<?php

namespace Tests\Services;

class RandomIntService
{
    public function getOne(): int
    {
        return 1;
    }

    public function getTwo(): int
    {
        return 2;
    }

    public function get(int $number): int
    {
        return $number;
    }
}
