<?php

namespace App\Services;

class LocationService
{
    private const EARTH_RADIUS_METERS = 6371000;

    public static function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);

        $dlat = $lat2 - $lat1;
        $dlng = $lng2 - $lng1;

        $a = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    public static function isWithinRadius(float $lat1, float $lng1, float $lat2, float $lng2, int $radiusMeters): bool
    {
        return self::distance($lat1, $lng1, $lat2, $lng2) <= $radiusMeters;
    }
}
