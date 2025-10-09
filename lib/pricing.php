<?php
/**
 * Pricing Calculator
 * Fixed pricing table based on devices and duration
 */

class PricingCalculator {
    /**
     * Fixed pricing table
     * [devices][months] = price
     */
    private static $pricingTable = [
        1 => [  // 1 Device
            1 => 2500,
            6 => 14000,
            12 => 28000
        ],
        2 => [  // 2 Devices
            1 => 4500,
            6 => 27000,
            12 => 54000
        ],
        3 => [  // 3 Devices
            1 => 6500,
            6 => 39000,
            12 => 78000
        ]
    ];

    /**
     * Get price for specific device count and duration
     * 
     * @param int $devices Number of devices (1-3)
     * @param int $months Duration in months (1, 6, or 12)
     * @return int|null Price in KES or null if not found
     */
    public static function getPrice($devices, $months) {
        // Validate devices
        if ($devices < 1 || $devices > 3) {
            return null;
        }

        // Normalize months to available tiers
        if ($months < 6) {
            $months = 1;
        } elseif ($months < 12) {
            $months = 6;
        } else {
            $months = 12;
        }

        return self::$pricingTable[$devices][$months] ?? null;
    }

    /**
     * Get all prices for a specific duration (month)
     * Returns array of [devices => price]
     */
    public static function getPricesForDuration($months) {
        // Normalize months
        if ($months < 6) {
            $months = 1;
        } elseif ($months < 12) {
            $months = 6;
        } else {
            $months = 12;
        }

        return [
            1 => self::$pricingTable[1][$months],
            2 => self::$pricingTable[2][$months],
            3 => self::$pricingTable[3][$months]
        ];
    }

    /**
     * Get package display price based on admin-entered duration
     * This calculates the correct price for the package's duration and selected devices
     */
    public static function getPackagePrice($durationDays, $devices = 1) {
        $months = self::convertDaysToMonths($durationDays);
        return self::getPrice($devices, $months);
    }

    /**
     * Convert days to months tier (1, 6, or 12)
     */
    public static function convertDaysToMonths($days) {
        $months = round($days / 30);
        
        if ($months < 6) {
            return 1;
        } elseif ($months < 12) {
            return 6;
        } else {
            return 12;
        }
    }

    /**
     * Get maximum allowed devices for a duration
     */
    public static function getMaxDevices($months) {
        // All durations support max 3 devices
        return 3;
    }

    /**
     * Check if device count is valid
     */
    public static function isValidDeviceCount($devices) {
        return $devices >= 1 && $devices <= 3;
    }

    /**
     * Get pricing table for display
     */
    public static function getPricingTable() {
        return self::$pricingTable;
    }

    /**
     * Get price breakdown for transparency
     */
    public static function getPriceBreakdown($devices, $months) {
        $price = self::getPrice($devices, $months);
        if (!$price) {
            return null;
        }

        $perMonth = $price / $months;
        
        return [
            'total' => $price,
            'per_month' => $perMonth,
            'months' => $months,
            'devices' => $devices,
            'formatted_total' => number_format($price, 0),
            'formatted_per_month' => number_format($perMonth, 0)
        ];
    }
}

