<?php

namespace App\Services;

use App\Models\{Branch, User, AnomalyLog};

/**
 * خدمة السياج الجغرافي
 * تستخدم معادلة Haversine لحساب المسافة بين إحداثيين على الكرة الأرضية.
 */
class GeofencingService
{
    private const EARTH_RADIUS_METERS = 6_371_000;

    /**
     * التحقق من أن موقع الموظف داخل نطاق الفرع.
     *
     * @return array{within_geofence: bool, distance_meters: float, allowed_radius: int}
     */
    public function validatePosition(
        Branch $branch,
        float  $userLat,
        float  $userLng
    ): array {
        $distance = $this->haversineDistance(
            (float) $branch->latitude,
            (float) $branch->longitude,
            $userLat,
            $userLng
        );

        $allowedRadius = $branch->geofence_radius ?? 17;

        return [
            'within_geofence' => $distance <= $allowedRadius,
            'distance_meters' => round($distance, 2),
            'allowed_radius'  => $allowedRadius,
        ];
    }

    /**
     * حساب المسافة بين نقطتين جغرافيتين (بالمتر) باستخدام Haversine.
     *
     * الصيغة الرياضية:
     * d = 2R × arcsin(√(sin²(Δφ/2) + cos(φ₁)·cos(φ₂)·sin²(Δλ/2)))
     */
    public function haversineDistance(
        float $lat1, float $lng1,
        float $lat2, float $lng2
    ): float {
        $φ1 = deg2rad($lat1);
        $φ2 = deg2rad($lat2);
        $Δφ = deg2rad($lat2 - $lat1);
        $Δλ = deg2rad($lng2 - $lng1);

        $a = sin($Δφ / 2) ** 2
           + cos($φ1) * cos($φ2) * sin($Δλ / 2) ** 2;

        $c = 2 * asin(sqrt($a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    /**
     * كشف السفر المستحيل (تسجيلان من موقعين بعيدين في وقت قصير).
     */
    public function detectImpossibleTravel(
        User  $user,
        float $newLat,
        float $newLng,
        int   $windowMinutes = 30
    ): bool {
        $lastLog = $user->attendanceLogs()
            ->whereNotNull('check_in_lat')
            ->where('check_in_at', '>=', now()->subMinutes($windowMinutes))
            ->latest('check_in_at')
            ->first();

        if (! $lastLog) {
            return false;
        }

        $distance = $this->haversineDistance(
            (float) $lastLog->check_in_lat,
            (float) $lastLog->check_in_lng,
            $newLat,
            $newLng
        );

        // إذا كان المستخدم بعيداً أكثر من 2 كم خلال 30 دقيقة فهو مشبوه
        $suspicious = $distance > 2000;

        if ($suspicious) {
            AnomalyLog::create([
                'user_id'     => $user->id,
                'type'        => 'impossible_travel',
                'description' => "تسجيل حضور من مسافة {$distance} متراً في أقل من {$windowMinutes} دقيقة",
                'metadata'    => [
                    'prev_lat'  => $lastLog->check_in_lat,
                    'prev_lng'  => $lastLog->check_in_lng,
                    'new_lat'   => $newLat,
                    'new_lng'   => $newLng,
                    'distance'  => $distance,
                    'window_min' => $windowMinutes,
                ],
                'severity'    => 'high',
            ]);
        }

        return $suspicious;
    }
}
