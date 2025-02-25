<?php

namespace App\Data;

use App\Models\DayLimit;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class DayLimitData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public string $start,
        public string $end,
        public string $backgroundColor,
        public string $borderColor,
        public string $allDay,
    ) {
    }

    public static function fromModel(DayLimit $dayLimit): self
    {
        return new self(
            $dayLimit->id,
            $dayLimit->name,
            $dayLimit->start->format("Y-m-d"),
            $dayLimit->finish->format("Y-m-d"),
            '#f87171',
            '#f87171',
            true
        );
    }
}
