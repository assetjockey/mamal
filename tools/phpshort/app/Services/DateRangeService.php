<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;

class DateRangeService
{
    /**
     * The maximum number of days for the date range limit.
     */
    private int $maxRangeDaysLimit = 36500;

    /**
     * The threshold in days for the hour unit.
     */
    private int $hourUnitDaysLimit = 1;

    /**
     * The threshold in days for the day unit.
     */
    private int $dayUnitDaysLimit = 90;

    /**
     * The threshold in days for the month unit.
     */
    private int $monthUnitDaysLimit = 730;

    /**
     * Build a normalized date range with aggregation unit and comparison period.
     */
    public function build(?Carbon $from = null, ?Carbon $to = null): array
    {
        $now = Carbon::now()->startOfDay();

        if ($to) {
            $to = $to->startOfDay();
        } else {
            if (request()->input('to')) {
                try {
                    $to = Carbon::createFromFormat('Y-m-d', request()->input('to'));
                } catch (Exception) {
                    $to = $now;
                }
            } else {
                $to = $now;
            }
        }

        if ($from) {
            $from = $from->startOfDay();
        } else {
            if (request()->input('from')) {
                try {
                    $from = Carbon::createFromFormat('Y-m-d', request()->input('from'));
                } catch (Exception) {
                    $from = $to;
                }
            } else {
                $from = $to;
            }
        }

        if ($from->gt($to)) {
            $from = $to;
        }

        if ($from->diffInDays($to) >= $this->maxRangeDaysLimit) {
            $to = $now;
            $from = $now;
        }

        if ($from->diffInDays($to) < $this->hourUnitDaysLimit) {
            $unit = 'hour';
            $format = 'Y-m-d H';
        } elseif ($from->diffInDays($to) < $this->dayUnitDaysLimit) {
            $unit = 'day';
            $format = 'Y-m-d';
        } elseif ($from->diffInDays($to) < $this->monthUnitDaysLimit) {
            $unit = 'month';
            $format = 'Y-m';
        } else {
            $unit = 'year';
            $format = 'Y';
        }

        $to_old = (clone $from)->subDays(1);
        $from_old = (clone $to_old)->subDays($from->diffInDays($to));

        return [
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'from_old' => $from_old->format('Y-m-d'),
            'to_old' => $to_old->format('Y-m-d'),
            'unit' => $unit,
            'format' => $format
        ];
    }

    /**
     * Generate time buckets between two dates at a given granularity.
     */
    public function generateTimeBuckets(string $from, string $to, string $unit, string $format, mixed $output = 0): array
    {
        if ($unit == 'second') {
            $from = Carbon::createFromFormat($format, $from);
            $to = Carbon::createFromFormat($format, $to);
        } else {
            $from = Carbon::createFromFormat($format, $from)->startOfDay();
            $to = Carbon::createFromFormat($format, $to)->endOfDay();
        }

        $possibleDateResults[$from->copy()->format($format)] = $output;

        while ($from->lt($to)) {
            if ($unit == 'year') {
                $from = $from->startOfYear()->addYears(1);
            } elseif ($unit == 'month') {
                $from = $from->startOfMonth()->addMonths(1);
            } elseif ($unit == 'day') {
                $from = $from->addDays(1);
            } elseif ($unit == 'hour') {
                $from = $from->addHours(1);
            } elseif ($unit == 'second') {
                $from = $from->addSeconds(1);
            }

            if ($from->lte($to)) {
                $possibleDateResults[$from->copy()->format($format)] = $output;
            }
        }

        return $possibleDateResults;
    }
}