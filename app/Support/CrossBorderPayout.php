<?php

namespace App\Support;

/**
 * Cross-border rent collection and repatriation rules.
 *
 * Rent is always collected in the property's residence currency. Landlords with
 * properties outside US/CA must register a local collection bank — we do not
 * wire France→US directly (tax/compliance). US/CA corridors may use home accounts
 * when property and home are both in North America.
 */
class CrossBorderPayout
{
    /** Property countries where local collection bank is optional (direct home repat common). */
    public const NORTH_AMERICA_CORRIDOR = ['US', 'CA'];

    public static function requiresLocalCollectionAccount(string $propertyCountryCode): bool
    {
        return ! in_array(strtoupper($propertyCountryCode), self::NORTH_AMERICA_CORRIDOR, true);
    }

    /** True when landlord should maintain a separate home-country repatriation account. */
    public static function requiresRepatriationAccount(string $propertyCountryCode, string $homeCountryCode): bool
    {
        $property = strtoupper($propertyCountryCode);
        $home     = strtoupper($homeCountryCode);

        if ($property === $home) {
            return false;
        }

        // Both US and CA: simpler corridor, local collection not mandated by platform rules.
        if (in_array($property, self::NORTH_AMERICA_CORRIDOR, true)
            && in_array($home, self::NORTH_AMERICA_CORRIDOR, true)) {
            return false;
        }

        return true;
    }

    public static function purposeLabel(string $purpose): string
    {
        return match ($purpose) {
            'collection'    => 'Local collection',
            'repatriation'  => 'Home repatriation',
            default         => ucfirst($purpose),
        };
    }
}
