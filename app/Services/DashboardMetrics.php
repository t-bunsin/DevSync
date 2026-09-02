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
    /** The status an application wears until an employer touches it. */
    private const STATUS_UNTOUCHED = JobApplication::STATUS_NEW;

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

    /** Hero spark geometry, matching the viewBox="0 0 240 46" in home.blade.php. */
    private const PULSE_DAYS = 14;
    private const PULSE_X_START = 2;
    private const PULSE_X_STEP = 18.15;
    private const PULSE_Y_TOP = 5;
    private const PULSE_Y_BOTTOM = 41;

    /** How long an untouched application may sit before it counts as overdue. */
    private const REVIEW_SLA_HOURS = 48;

    /** The window every hero figure except "today" is measured over. */
    private const PULSE_WINDOW_DAYS = 30;

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
            'points' => $this->polyline(
                $this->weeklyBuckets($daily),
                self::SPARK_X_START,
                self::SPARK_X_STEP,
                self::SPARK_Y_TOP,
                self::SPARK_Y_BOTTOM,
            ),
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
     * The figures behind the dashboard's command banner.
     *
     * Everything the banner says is measured here rather than written into the
     * view, because the banner makes claims — "moving faster", "needs
     * attention" — and a claim that does not follow the data is worse than no
     * banner at all. When there is nothing to measure the numbers come back
     * null and the view says so instead of printing a confident zero.
     *
     * @return array{
     *     has_data: bool, today: int, yesterday: int, today_delta: int,
     *     growth: float|null, response_hours: float|null, overdue: int,
     *     awaiting: int, score: int|null, tone: string, trend: string, spark: string|null
     * }
     */
    public function command(): array
    {
        $daily = $this->dailyCounts(
            JobApplication::query(),
            self::APPLIED_AT,
            $this->now->subDays(self::PULSE_DAYS)->startOfDay(),
        );

        $today = $daily[$this->now->toDateString()] ?? 0;
        $yesterday = $daily[$this->now->subDay()->toDateString()] ?? 0;
        $windowTotal = array_sum($daily);

        // Nothing arrived in either week, so there is no growth to report —
        // not "no change". percentChange() cannot tell those apart on its own
        // because a flat zero is a real answer to the cards asking it.
        $growth = $windowTotal === 0
            ? null
            : $this->percentChange(
                $this->sumBetween($daily, $this->now->subDays(6)->startOfDay(), $this->now),
                $this->sumBetween($daily, $this->now->subDays(13)->startOfDay(), $this->now->subDays(7)),
            );

        $responseHours = $this->averageResponseHours();
        $overdue = $this->overdueCount();
        $awaiting = JobApplication::query()->where('status', self::STATUS_UNTOUCHED)->count();
        $reviewedRate = $this->reviewedRate();

        // No applications in the whole window: there is nothing to score, and
        // a made-up number here would be the one figure nobody could check.
        $hasData = $reviewedRate !== null;
        $score = $hasData ? $this->healthScore($reviewedRate, $responseHours, $awaiting, $overdue) : null;

        return [
            'has_data' => $hasData,
            'today' => $today,
            'yesterday' => $yesterday,
            'today_delta' => $today - $yesterday,
            'growth' => $growth,
            'response_hours' => $responseHours,
            'overdue' => $overdue,
            'awaiting' => $awaiting,
            'score' => $score,
            'tone' => match (true) {
                $score === null => 'quiet',
                $score >= 75 => 'strong',
                $score >= 50 => 'steady',
                default => 'watch',
            },
            'trend' => match (true) {
                ! $hasData => 'quiet',
                $overdue > 0 && ($growth === null || $growth <= 0) => 'backlog',
                $growth !== null && $growth >= 5 => 'up',
                $growth !== null && $growth <= -5 => 'down',
                default => 'steady',
            },
            // A fortnight of zeroes draws as a filled bar along the floor,
            // which reads as data. Null, and the view leaves the space empty.
            'spark' => $windowTotal === 0 ? null : $this->polyline(
                $this->dailyBuckets($daily, self::PULSE_DAYS),
                self::PULSE_X_START,
                self::PULSE_X_STEP,
                self::PULSE_Y_TOP,
                self::PULSE_Y_BOTTOM,
            ),
        ];
    }

    /**
     * Mean hours between an application arriving and an employer first moving
     * it off 'new', over the pulse window.
     *
     * The subtraction is done in PHP rather than in SQL: date arithmetic is
     * spelled differently by every driver (TIMESTAMPDIFF, EXTRACT, julianday),
     * and a month of applications is a small enough set to walk. Null when
     * nobody has answered anything yet — an average of no answers is not zero
     * hours, it is no answer.
     */
    private function averageResponseHours(): ?float
    {
        $rows = JobApplication::query()
            ->selectRaw(self::APPLIED_AT . ' as arrived_at, status_changed_at')
            ->whereNotNull('status_changed_at')
            ->whereRaw('status_changed_at >= ?', [$this->now->subDays(self::PULSE_WINDOW_DAYS)->toDateTimeString()])
            ->toBase()
            ->get();

        $hours = [];

        foreach ($rows as $row) {
            $arrived = CarbonImmutable::parse((string) $row->arrived_at);
            $answered = CarbonImmutable::parse((string) $row->status_changed_at);

            // Guards a backfilled status_changed_at that predates the row it
            // belongs to; a negative wait would drag the mean below zero.
            if ($answered->greaterThanOrEqualTo($arrived)) {
                $hours[] = $arrived->diffInMinutes($answered) / 60;
            }
        }

        return $hours === [] ? null : round(array_sum($hours) / count($hours), 1);
    }

    /** Applications still untouched past the review SLA. */
    private function overdueCount(): int
    {
        return JobApplication::query()
            ->where('status', self::STATUS_UNTOUCHED)
            ->whereRaw(self::APPLIED_AT . ' < ?', [$this->now->subHours(self::REVIEW_SLA_HOURS)->toDateTimeString()])
            ->count();
    }

    /**
     * Share of the window's applications an employer has actually opened.
     * Null when the window is empty, which is what tells command() there is
     * nothing to score.
     */
    private function reviewedRate(): ?float
    {
        $since = $this->now->subDays(self::PULSE_WINDOW_DAYS)->toDateTimeString();

        $total = JobApplication::query()->whereRaw(self::APPLIED_AT . ' >= ?', [$since])->count();

        if ($total === 0) {
            return null;
        }

        $reviewed = JobApplication::query()
            ->whereRaw(self::APPLIED_AT . ' >= ?', [$since])
            ->where('status', '!=', self::STATUS_UNTOUCHED)
            ->count();

        return $reviewed / $total;
    }

    /**
     * One number for "is hiring being kept on top of", 0-100.
     *
     * Three things, weighted by how much a candidate would feel them: whether
     * anyone opened the application at all, how long they waited, and how much
     * has been left sitting past the SLA. Speed is scored against the SLA, so
     * answering inside 48 hours scores full marks and taking a week does not.
     */
    private function healthScore(float $reviewedRate, ?float $responseHours, int $awaiting, int $overdue): int
    {
        // Unanswered so far is not the same as answered slowly: an employer
        // with no decisions yet scores the middle, not zero.
        $speed = $responseHours === null
            ? 0.5
            : max(0.0, min(1.0, 1 - ($responseHours / (self::REVIEW_SLA_HOURS * 2))));

        $backlog = $awaiting === 0 ? 1.0 : 1 - ($overdue / $awaiting);

        return (int) round((($reviewedRate * 0.5) + ($speed * 0.3) + ($backlog * 0.2)) * 100);
    }

    /**
     * The last N whole days, oldest first — the weekly buckets' daily twin.
     *
     * @param  array<string, int>  $daily
     * @return list<int>
     */
    private function dailyBuckets(array $daily, int $days): array
    {
        $buckets = [];

        for ($day = $days - 1; $day >= 0; $day--) {
            $buckets[] = $daily[$this->now->subDays($day)->toDateString()] ?? 0;
        }

        return $buckets;
    }

    /**
     * Scales the buckets into an SVG box. The series is normalised to its own
     * range, so a metric counting single digits draws the same shape a metric
     * counting thousands would — these are trend lines, not a scale.
     *
     * The box is passed in rather than fixed: the four cards and the hero
     * spark draw the same way into different viewBoxes.
     *
     * @param  list<int|float>  $values
     */
    private function polyline(array $values, float $xStart, float $xStep, float $yTop, float $yBottom): string
    {
        $min = min($values);
        $max = max($values);
        $span = $max - $min;
        $middle = ($yTop + $yBottom) / 2;

        $points = [];

        foreach (array_values($values) as $index => $value) {
            $x = $xStart + ($index * $xStep);

            // A flat series has no shape to draw, so it rides the middle rather
            // than pinning to the top or bottom of the box.
            $y = $span == 0
                ? $middle
                : $yBottom - (($value - $min) / $span) * ($yBottom - $yTop);

            $points[] = round($x, 1) . ',' . round($y, 1);
        }

        return implode(' ', $points);
    }
}
