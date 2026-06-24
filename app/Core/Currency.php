<?php

namespace App\Core;

use App\Models\Setting;

class Currency
{
    /**
     * Toutes les devises supportées avec leurs symboles et taux de fallback par rapport au USD.
     */
    public static function all(): array
    {
        return [
            'USD' => ['name' => 'Dollar Américain',    'symbol' => '$',    'flag' => '🇺🇸', 'rate_key' => null,           'fallback' => 1],
            'CDF' => ['name' => 'Franc Congolais',     'symbol' => 'FC',   'flag' => '🇨🇩', 'rate_key' => 'usd_rate_cdf', 'fallback' => 2800],
            'XAF' => ['name' => 'Franc CFA (CEMAC)',   'symbol' => 'FCFA', 'flag' => '🌍',  'rate_key' => 'usd_rate_xaf', 'fallback' => 620],
            'XOF' => ['name' => 'Franc CFA (UEMOA)',   'symbol' => 'FCFA', 'flag' => '🌍',  'rate_key' => 'usd_rate_xof', 'fallback' => 620],
            'RWF' => ['name' => 'Franc Rwandais',      'symbol' => 'FRw',  'flag' => '🇷🇼', 'rate_key' => 'usd_rate_rwf', 'fallback' => 1300],
            'BIF' => ['name' => 'Franc Burundais',     'symbol' => 'FBu',  'flag' => '🇧🇮', 'rate_key' => 'usd_rate_bif', 'fallback' => 2900],
            'UGX' => ['name' => 'Shilling Ougandais',  'symbol' => 'USh',  'flag' => '🇺🇬', 'rate_key' => 'usd_rate_ugx', 'fallback' => 3750],
            'TZS' => ['name' => 'Shilling Tanzanien',  'symbol' => 'TSh',  'flag' => '🇹🇿', 'rate_key' => 'usd_rate_tzs', 'fallback' => 2700],
            'KES' => ['name' => 'Shilling Kényan',     'symbol' => 'KSh',  'flag' => '🇰🇪', 'rate_key' => 'usd_rate_kes', 'fallback' => 130],
            'GNF' => ['name' => 'Franc Guinéen',       'symbol' => 'GF',   'flag' => '🇬🇳', 'rate_key' => 'usd_rate_gnf', 'fallback' => 8600],
            'NGN' => ['name' => 'Naira Nigérian',      'symbol' => '₦',    'flag' => '🇳🇬', 'rate_key' => 'usd_rate_ngn', 'fallback' => 1550],
            'ZAR' => ['name' => 'Rand Sud-Africain',   'symbol' => 'R',    'flag' => '🇿🇦', 'rate_key' => 'usd_rate_zar', 'fallback' => 18],
            'MAD' => ['name' => 'Dirham Marocain',     'symbol' => 'DH',   'flag' => '🇲🇦', 'rate_key' => 'usd_rate_mad', 'fallback' => 10],
            'EGP' => ['name' => 'Livre Égyptienne',    'symbol' => 'E£',   'flag' => '🇪🇬', 'rate_key' => 'usd_rate_egp', 'fallback' => 50],
            'EUR' => ['name' => 'Euro',                 'symbol' => '€',    'flag' => '🇪🇺', 'rate_key' => 'usd_rate_eur', 'fallback' => 0.92],
        ];
    }

    /**
     * Initialise la devise en session (USD par défaut)
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['currency']) || !array_key_exists($_SESSION['currency'], self::all())) {
            $_SESSION['currency'] = 'USD';
        }
    }

    /**
     * Récupère la devise active depuis la session
     */
    public static function getActive(): string
    {
        self::init();
        return $_SESSION['currency'];
    }

    /**
     * Définit la devise active dans la session
     */
    public static function setActive(string $currency): void
    {
        self::init();
        if (array_key_exists($currency, self::all())) {
            $_SESSION['currency'] = $currency;
        }
    }

    /**
     * Retourne le taux de conversion USD → devise donnée (ou active si null)
     */
    public static function getRate(?string $currency = null): float
    {
        $all      = self::all();
        $currency = $currency ?? self::getActive();
        if (!isset($all[$currency])) return 1.0;

        $info = $all[$currency];
        if ($info['rate_key']) {
            return (float) Setting::get($info['rate_key'], (string)$info['fallback']);
        }
        return (float) $info['fallback'];
    }

    /**
     * Convertit et formate un montant USD vers la devise active
     */
    public static function format(float $amountUsd, int $decimals = 2): string
    {
        $active = self::getActive();
        $all    = self::all();

        if ($active === 'USD') {
            return '$' . number_format($amountUsd, $decimals, '.', ' ');
        }

        $rate      = self::getRate($active);
        $converted = $amountUsd * $rate;
        $symbol    = $all[$active]['symbol'];

        // Pas de décimales pour les devises à grande valeur nominale
        $noDecimal = ['CDF', 'XAF', 'XOF', 'RWF', 'BIF', 'UGX', 'TZS', 'GNF', 'NGN'];
        $d = in_array($active, $noDecimal) ? 0 : 2;

        return number_format($converted, $d, ',', ' ') . ' ' . $symbol;
    }

    /**
     * Convertit un montant brut USD vers la devise active (valeur brute, sans formatage)
     */
    public static function convert(float $amountUsd): float
    {
        return $amountUsd * self::getRate();
    }

    /**
     * Renvoie le symbole monétaire actif
     */
    public static function symbol(): string
    {
        $all = self::all();
        return $all[self::getActive()]['symbol'] ?? '$';
    }
}
