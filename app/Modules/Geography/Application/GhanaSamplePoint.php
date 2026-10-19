<?php

namespace App\Modules\Geography\Application;

/**
 * Places a sample opportunity near its region's capital, inside Ghana.
 *
 * The catalogue file has names only, so these anchors are the published
 * regional capitals. A small offset keeps neighbouring districts apart
 * without walking the point into Togo or the Atlantic.
 */
final class GhanaSamplePoint
{
    /**
     * Latitude, longitude of the regional capital.
     *
     * @var array<string, array{0: float, 1: float}>
     */
    private const ANCHORS = [
        'AH' => [6.803, -2.517],
        'AS' => [6.688, -1.624],
        'BO' => [7.340, -2.326],
        'BE' => [7.582, -1.934],
        'CP' => [5.105, -1.247],
        'EP' => [6.094, -0.259],
        'AA' => [5.603, -0.187],
        'NP' => [9.407, -0.853],
        'NE' => [10.527, -0.370],
        'OT' => [8.066, 0.179],
        'SV' => [9.083, -1.818],
        'UE' => [10.786, -0.851],
        'UW' => [10.060, -2.501],
        'TV' => [6.611, 0.471],
        'WP' => [4.934, -1.713],
        'WN' => [6.216, -2.489],
    ];

    /**
     * @return array{0: float, 1: float}
     */
    public static function near(string $regionCode, int $salt): array
    {
        [$latitude, $longitude] = self::ANCHORS[$regionCode] ?? [7.946, -1.023];
        $latitude += (($salt % 7) - 3) * 0.035;
        $longitude += (($salt % 5) - 2) * 0.035;

        return [
            round(min(11.0, max(4.74, $latitude)), 6),
            round(min(1.05, max(-3.25, $longitude)), 6),
        ];
    }
}
