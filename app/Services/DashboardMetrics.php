<?php

namespace App\Services;

use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\Resume;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * The four headline figures on the admin dashboard, read from the real tables.
 *
 * Each card carries three numbers and they measure deliberately different
 * things: the headline is a running total, while the delta and the
 * sparkline both describe inflow — how many rows arrived in a period. A total
 * only ever climbs, so a trend drawn from it would be a straight line and tell
 * nobody anything; the arrivals behind it are the part worth watching.
 *
 * Bucketing is done by DATE() and rolled up into weeks in PHP rather than with
 * YEARWEEK(). That keeps at most ~57 rows coming back per metric and avoids a
 * MySQL-only function, so the same query works on the SQLite test connection.
 */
class DashboardMetrics
{
    /** Weekly buckets behind each sparkline. */
    private const SPARK_WEEKS = 8;

    /** Each delta compares this many days against the same span before it. */
    private const DELTA_DAYS = 30;

    /**
     * An application's own applied_at is the date the rest of the back office
     * shows; created_at only stands in for rows written before that column
     * existed. JobApplication::scopeDateRange() coalesces the same way.
     */
    private const APPLIED_AT = 'COALESCE(applied_at, created_at)';

    /** Sparkline geometry, matching the viewBox="0 0 116 44" in home.blade.php. */
    private const SPARK_X_START = 2;
    private const SPARK_X_STEP = 16;
    private const SPARK_Y_TOP = 5;
    private const SPARK_Y_BOTTOM = 38;

    private CarbonImmutable $now;

    public function __construct(?CarbonImmutable $now = null)
    {
        $this->now = $now ?? CarbonImmutable::now();
    }

    /**
     * @return array<string, array{value: int, delta: float|null, points: string}>
     */
    public function cards(): array
    {
        return [
            'users' => $this->countCard(User::query(), 'created_at', User::query()->count()),
            'open_roles' => $this->countCard(
                JobPost::query()->published(),
                'created_at',
                JobPost::query()->published()->count(),
            ),
            'applications' => $this->countCard(
                JobApplication::query(),
                self::APPLIED_AT,
                JobApplication::query()->count(),
            ),
            'resumes' => $this->countCard(Resume::query(), 'created_at', Resume::query()->count()),
        ];
    }

    /**
     * @return array{value: int, delta: float|null, points: string}
     */
    private function countCard(Builder $query, string $dateExpr, int $total): array
    {
        $daily = $this->dailyCounts($query, $dateExpr, $this->now->subWeeks(self::SPARK_WEEKS)->startOfWeek());

        $current = $this->sumBetween($daily, $this->now->subDays(self::DELTA_DAYS), $this->now);
        $previous = $this->sumBetween(
            $daily,
            $this->now->subDays(self::DELTA_DAYS * 2),
            $this->now->subDays(self::DELTA_DAYS),
        );

        return [
            'value' => $total,
            'delta' => $this->percentChange($current, $previous),
            'points' => $this->polyline($this->weeklyBuckets($daily)),
        ];
    }

    /**
     * One row per day in range: ['2026-08-31' => 4, ...]. Days with nothing are
     * absent rather than zero, so every reader has to tolerate gaps.
     *
     * @return array<string, int>
     */
    private function dailyCounts(Builder $query, string $dateExpr, CarbonImmutable $from): array
    {
        // toBase() applies the model's scopes and then hands back plain rows,
        // so nothing here depends on hydrating a model per day.
        return $query
            ->selectRaw("DATE({$dateExpr}) as bucket_day, COUNT(*) as bucket_count")
            ->whereRaw("{$dateExpr} >= ?", [$from->toDateTimeString()])
            ->groupBy('bucket_day')
            ->toBase()
            ->get()
            ->mapWithKeys(fn ($row): array => [(string) $row->bucket_day => (int) $row->bucket_count])
            ->all();
    }

    /**
     * @param  array<string, int>  $daily
     */
    private function sumBetween(array $daily, CarbonImmutable $from, CarbonImmutable $to): int
    {
        $fromDay = $from->toDateString();
        $toDay = $to->toDateString();

        $total = 0;

        foreach ($daily as $day => $count) {
            // Both ends inclusive: a window is a set of whole days here, and the
            // day the window opens on is as real as the day it closes on.
            if ($day >= $fromDay && $day <= $toDay) {
                $total += $count;
            }
        }

        return $total;
    }

    /**
     * The last SPARK_WEEKS whole weeks, oldest first, each one a plain total.
     *
     * @param  array<string, int>  $daily
     * @return list<int>
     */
    private function weeklyBuckets(array $daily): array
    {
        $buckets = [];

        for ($week = self::SPARK_WEEKS - 1; $week >= 0; $week--) {
            $start = $this->now->subWeeks($week)->startOfWeek();
            $end = $start->addDays(6);

            $buckets[] = $this->sumBetween($daily, $start, $end);
        }

        return $buckets;
    }

    /**
     * Null rather than a number when there is no baseline to compare against:
     * "up from nothing" is not a percentage, and the card hides its badge
     * instead of printing an invented +100%.
     */
    private function percentChange(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return $current === 0 ? 0.0 : null;
        }

        return round(($current - $previous) / $previous * 100, 1);
    }

    /**
     * Scales the buckets into the card's SVG box. The series is normalised to
     * its own range, so a metric counting single digits draws the same shape a
     * metric counting thousands would — these are trend lines, not a scale.
     *
     * @param  list<int|float>  $values
     */
    private function polyline(array $values): string
    {
        $min = min($values);
        $max = max($values);
        $span = $max - $min;
        $middle = (self::SPARK_Y_TOP + self::SPARK_Y_BOTTOM) / 2;

        $points = [];

        foreach (array_values($values) as $index => $value) {
            $x = self::SPARK_X_START + ($index * self::SPARK_X_STEP);

            // A flat series has no shape to draw, so it rides the middle rather
            // than pinning to the top or bottom of the box.
            $y = $span == 0
                ? $middle
                : self::SPARK_Y_BOTTOM - (($value - $min) / $span) * (self::SPARK_Y_BOTTOM - self::SPARK_Y_TOP);

            $points[] = $x . ',' . round($y, 1);
        }

        return implode(' ', $points);
    }
}
