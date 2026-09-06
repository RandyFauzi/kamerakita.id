<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Parse and normalize a given phone number to a valid E.164 format.
     * Defaults to Indonesia (+62) if no prefix is provided and it looks like a local number.
     * 
     * @param string $phone
     * @param string $countryCode ISO 3166-1 alpha-2 code (e.g. 'ID', 'US')
     * @return string|null The E.164 normalized number or null if invalid
     */
    public static function normalize(string $phone, string $countryCode = 'ID'): ?string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (empty($phone)) {
            return null;
        }

        // Special handling for legacy Indonesian numbers starting with '0'
        if ($countryCode === 'ID' && str_starts_with($phone, '0')) {
            return '+62' . substr($phone, 1);
        }

        if ($countryCode === 'ID' && str_starts_with($phone, '62')) {
            return '+' . $phone;
        }

        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        // If it does not start with +, fallback to prepend the country code
        $countries = config('countries');
        if (isset($countries[$countryCode])) {
            return $countries[$countryCode]['phone_code'] . $phone;
        }

        return '+' . $phone;
    }

    /**
     * Formats a phone number for APIs that require country code without the '+' symbol (like some WA gateways).
     */
    public static function formatForGateway(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }
        
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        if (str_starts_with($phone, '+')) {
            return substr($phone, 1);
        }
        
        // Handle legacy Indonesian numbers directly
        if (str_starts_with($phone, '0')) {
            return '62' . substr($phone, 1);
        }
        
        return $phone;
    }
}
