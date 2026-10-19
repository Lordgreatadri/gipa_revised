<?php

namespace App\Modules\PublicPortal\Application;

use App\Modules\PublicPortal\Domain\GhanaPublicHolidays;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * GIPA desk hours: Monday to Friday, 08:00 to 17:00 Africa/Accra.
 * Weekends and public holidays are closed.
 */
final class OfficeHours
{
    public function __construct(private readonly GhanaPublicHolidays $holidays) {}

    /**
     * @return array{
     *     open: bool,
     *     headline: string,
     *     detail: string,
     *     holiday: string|null,
     *     days: list<array{label: string, hours: string, today: bool, shut: bool}>
     * }
     */
    public function snapshot(?CarbonInterface $moment = null): array
    {
        $zone = (string) config('public.office.timezone', 'Africa/Accra');
        $now = CarbonImmutable::parse($moment ?? CarbonImmutable::now($zone))->timezone($zone);
        $holiday = $this->holidays->nameOn($now);
        $open = $holiday === null && $now->isWeekday() && $this->insideHours($now);
        $next = $this->nextOpening($now);

        return [
            'open' => $open,
            'headline' => $open ? 'Open now' : 'Closed',
            'detail' => $this->detail($now, $open, $holiday, $next),
            'holiday' => $holiday,
            'days' => $this->week($now),
        ];
    }

    private function insideHours(CarbonImmutable $now): bool
    {
        $opens = $this->minutes((string) config('public.office.opens', '08:00'));
        $closes = $this->minutes((string) config('public.office.closes', '17:00'));
        $current = ($now->hour * 60) + $now->minute;

        return $current >= $opens && $current < $closes;
    }

    private function detail(CarbonImmutable $now, bool $open, ?string $holiday, CarbonImmutable $next): string
    {
        if ($open) {
            return 'Closes today at '.$this->closesLabel().'.';
        }

        $when = $next->isSameDay($now)
            ? 'today at '.$this->opensLabel()
            : $next->format('l').' at '.$this->opensLabel();

        if ($holiday !== null) {
            return 'Closed for '.$holiday.'. Opens '.$when.'.';
        }

        if ($now->isWeekend()) {
            return 'Closed for the weekend. Opens '.$when.'.';
        }

        if ($this->minutes((string) config('public.office.opens', '08:00')) > ($now->hour * 60) + $now->minute) {
            return 'Opens today at '.$this->opensLabel().'.';
        }

        return 'Closed for today. Opens '.$when.'.';
    }

    private function nextOpening(CarbonImmutable $now): CarbonImmutable
    {
        $cursor = $now;

        if ($this->holidays->nameOn($cursor) === null && $cursor->isWeekday()) {
            $opensAt = $cursor->setTimeFromTimeString((string) config('public.office.opens', '08:00'));

            if ($now->lessThan($opensAt)) {
                return $opensAt;
            }
        }

        for ($step = 1; $step <= 21; $step++) {
            $day = $now->addDays($step)->setTimeFromTimeString((string) config('public.office.opens', '08:00'));

            if ($day->isWeekday() && $this->holidays->nameOn($day) === null) {
                return $day;
            }
        }

        return $now->addDay()->setTimeFromTimeString((string) config('public.office.opens', '08:00'));
    }

    /**
     * @return list<array{label: string, hours: string, today: bool, shut: bool}>
     */
    private function week(CarbonImmutable $now): array
    {
        $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $start = $now->startOfWeek(CarbonImmutable::MONDAY);
        $days = [];

        foreach ($labels as $index => $label) {
            $day = $start->addDays($index);
            $shut = $day->isWeekend() || $this->holidays->nameOn($day) !== null;
            $days[] = [
                'label' => $label,
                'hours' => $shut ? 'Closed' : '8–5',
                'today' => $day->isSameDay($now),
                'shut' => $shut,
            ];
        }

        return $days;
    }

    private function opensLabel(): string
    {
        return CarbonImmutable::parse((string) config('public.office.opens', '08:00'))->format('g:i a');
    }

    private function closesLabel(): string
    {
        return CarbonImmutable::parse((string) config('public.office.closes', '17:00'))->format('g:i a');
    }

    private function minutes(string $clock): int
    {
        [$hour, $minute] = array_pad(explode(':', $clock, 2), 2, '0');

        return ((int) $hour * 60) + (int) $minute;
    }
}
