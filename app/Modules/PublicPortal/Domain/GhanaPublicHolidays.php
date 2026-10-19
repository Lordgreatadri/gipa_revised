<?php

namespace App\Modules\PublicPortal\Domain;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Statutory public holidays observed in Ghana. Eid al-Fitr and Eid al-Adha
 * follow the lunar calendar and are added as dated closures in config when
 * the dates are announced. A holiday that falls on a weekend is observed on
 * the next weekday that is not already a holiday.
 */
final class GhanaPublicHolidays
{
    public function nameOn(CarbonInterface $day): ?string
    {
        $local = CarbonImmutable::parse($day)->timezone($this->timezone());
        $calendar = $this->calendarFor((int) $local->year);

        return $calendar[$local->toDateString()] ?? null;
    }

    /**
     * @return array<string, string>
     */
    private function calendarFor(int $year): array
    {
        $named = [];

        foreach ([$year - 1, $year, $year + 1] as $candidate) {
            foreach ($this->statutory($candidate) as $date => $name) {
                $named[$date] = $name;
            }
        }

        foreach ($this->extra() as $date => $name) {
            $named[(string) $date] = (string) $name;
        }

        return $this->withObservedWeekends($named);
    }

    /**
     * @return array<string, string>
     */
    private function statutory(int $year): array
    {
        $zone = $this->timezone();
        $easter = CarbonImmutable::create($year, 3, 21, 0, 0, 0, $zone)->addDays(easter_days($year));
        $december = CarbonImmutable::create($year, 12, 1, 0, 0, 0, $zone);
        $farmers = $december->dayOfWeek === CarbonImmutable::FRIDAY
            ? $december
            : $december->next(CarbonImmutable::FRIDAY);

        $fixed = [
            '01-01' => "New Year's Day",
            '01-07' => 'Constitution Day',
            '03-06' => 'Independence Day',
            '05-01' => 'May Day',
            '08-04' => "Founders' Day",
            '09-21' => 'Kwame Nkrumah Memorial Day',
            '12-25' => 'Christmas Day',
            '12-26' => 'Boxing Day',
        ];

        $dates = [];

        foreach ($fixed as $monthDay => $name) {
            $dates[$year.'-'.$monthDay] = $name;
        }

        $dates[$easter->subDays(2)->toDateString()] = 'Good Friday';
        $dates[$easter->addDay()->toDateString()] = 'Easter Monday';
        $dates[$farmers->toDateString()] = "Farmers' Day";

        return $dates;
    }

    /**
     * @param  array<string, string>  $named
     * @return array<string, string>
     */
    private function withObservedWeekends(array $named): array
    {
        $observed = $named;

        foreach ($named as $date => $name) {
            $day = CarbonImmutable::parse($date, $this->timezone());

            if (! $day->isWeekend() || str_ends_with($name, '(observed)')) {
                continue;
            }

            $next = $day->next(CarbonImmutable::MONDAY);

            while (isset($observed[$next->toDateString()])) {
                $next = $next->addDay();

                if ($next->isWeekend()) {
                    $next = $next->next(CarbonImmutable::MONDAY);
                }
            }

            $observed[$next->toDateString()] = $name.' (observed)';
        }

        return $observed;
    }

    /**
     * @return array<string, string>
     */
    private function extra(): array
    {
        return (array) config('public.office.extra_closures', []);
    }

    private function timezone(): string
    {
        return (string) config('public.office.timezone', 'Africa/Accra');
    }
}
