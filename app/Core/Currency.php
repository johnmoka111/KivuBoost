<?php

namespace App\Core;

use App\Models\Setting;

class Currency
{
    /**
     * Initialise la devise en session
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['currency'])) {
            $_SESSION['currency'] = 'USD';
        }
    }

    /**
     * Récupère la devise active ('USD' ou 'CDF')
     */
    public static function getActive(): string
    {
        self::init();
        return $_SESSION['currency'];
    }

    /**
     * Change la devise active
     */
    public static function setActive(string $currency): void
    {
        self::init();
        if (in_array($currency, ['USD', 'CDF'])) {
            $_SESSION['currency'] = $currency;
        }
    }

    /**
     * Convertit et formate un montant en USD vers la devise active
     */
    public static function format(float $amountUsd, int $decimals = 2): string
    {
        $active = self::getActive();
        if ($active === 'CDF') {
            // Récupérer le taux dynamiquement de la base de données via Setting
            $rate = (float)Setting::get('usd_rate_cdf', '2800');
            $amountCdf = $amountUsd * $rate;
            // Arrondir sans décimales pour les Francs Congolais (standard)
            return number_format($amountCdf, 0, ',', ' ') . ' CDF';
        }

        // Format standard USD ($ 10.00)
        return '$' . number_format($amountUsd, $decimals, '.', ' ');
    }

    /**
     * Convertit un montant brut de USD en CDF si actif, sinon garde USD
     */
    public static function convert(float $amountUsd): float
    {
        $active = self::getActive();
        if ($active === 'CDF') {
            $rate = (float)Setting::get('usd_rate_cdf', '2800');
            return $amountUsd * $rate;
        }
        return $amountUsd;
    }

    /**
     * Renvoie le symbole monétaire actif
     */
    public static function symbol(): string
    {
        return self::getActive() === 'CDF' ? 'CDF' : '$';
    }
}
