<?php

namespace App\Services;

class SmmApi
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct(string $apiKey, string $apiUrl)
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = rtrim($apiUrl, '/');
    }

    /**
     * Compatibilité descendante avec l'architecture existante de KivuBoost
     */
    public function addOrder(int $serviceId, string $link, int $quantity): array
    {
        $res = $this->order([
            'service' => $serviceId,
            'link'    => $link,
            'quantity' => $quantity
        ]);
        return is_array($res) ? $res : (array)$res;
    }

    public function getStatus(string $orderId): array
    {
        $res = $this->status($orderId);
        return is_array($res) ? $res : (array)$res;
    }

    public function getBalance(): array
    {
        $res = $this->balance();
        return is_array($res) ? $res : (array)$res;
    }

    public function getServices(): array
    {
        $res = $this->services();
        return is_array($res) ? $res : (array)$res;
    }

    // ========================================================
    // MÉTHODES FOURNIES PAR L'UTILISATEUR (ADAPTÉES DYNAMIQUEMENT)
    // ========================================================

    /** Ajouter une commande */
    public function order($data)
    {
        $post = array_merge(['key' => $this->apiKey, 'action' => 'add'], $data);
        return json_decode((string)$this->connect($post), true);
    }

    /** Obtenir le statut de la commande */
    public function status($order_id)
    {
        return json_decode(
            (string)$this->connect([
                'key' => $this->apiKey,
                'action' => 'status',
                'order' => $order_id
            ]),
            true
        );
    }

    /** Obtenir le statut de plusieurs commandes */
    public function multiStatus($order_ids)
    {
        return json_decode(
            (string)$this->connect([
                'key' => $this->apiKey,
                'action' => 'status',
                'orders' => implode(",", (array)$order_ids)
            ]),
            true
        );
    }

    /** Obtenir la liste des services */
    public function services()
    {
        return json_decode(
            (string)$this->connect([
                'key' => $this->apiKey,
                'action' => 'services',
            ]),
            true
        );
    }

    /** Commande de recharge / Refill */
    public function refill(int $orderId)
    {
        return json_decode(
            (string)$this->connect([
                'key' => $this->apiKey,
                'action' => 'refill',
                'order' => $orderId,
            ]),
            true
        );
    }

    /** Recharges multiples / Multi Refill */
    public function multiRefill(array $orderIds)
    {
        return json_decode(
            (string)$this->connect([
                'key' => $this->apiKey,
                'action' => 'refill',
                'orders' => implode(',', $orderIds),
            ]),
            true
        );
    }

    /** Obtenir l'état du réapprovisionnement / Refill status */
    public function refillStatus(int $refillId)
    {
         return json_decode(
            (string)$this->connect([
                'key' => $this->apiKey,
                'action' => 'refill_status',
                'refill' => $refillId,
            ]),
            true
        );
    }

    /** Obtenir l'état des recharges multiples */
    public function multiRefillStatus(array $refillIds)
    {
         return json_decode(
            (string)$this->connect([
                'key' => $this->apiKey,
                'action' => 'refill_status',
                'refills' => implode(',', $refillIds),
            ]),
            true
        );
    }

    /** Annulation de commandes multiples */
    public function cancel(array $orderIds)
    {
        return json_decode(
            (string)$this->connect([
                'key' => $this->apiKey,
                'action' => 'cancel',
                'orders' => implode(',', $orderIds),
            ]),
            true
        );
    }

    /** Obtenir le solde restant */
    public function balance()
    {
        return json_decode(
            (string)$this->connect([
                'key' => $this->apiKey,
                'action' => 'balance',
            ]),
            true
        );
    }

    /**
     * Méthode de communication cURL robuste avec désactivation du SSL
     * pour garantir le fonctionnement en local sous Windows XAMPP
     */
    private function connect($post)
    {
        $_post = [];
        if (is_array($post)) {
            foreach ($post as $name => $value) {
                $_post[] = $name . '=' . urlencode($value);
            }
        }

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        
        // Détecter l'environnement : sécurité stricte en production, souplesse en local (XAMPP)
        $isLocalhost = in_array(explode(':', $_SERVER['HTTP_HOST'] ?? 'localhost')[0], ['localhost', '127.0.0.1', '[::1]']);
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $isLocalhost ? 0 : 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $isLocalhost ? 0 : 2);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (is_array($post)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $_post));
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $result = curl_exec($ch);
        if (curl_errno($ch) != 0 && empty($result)) {
            $result = false;
        }
        curl_close($ch);
        return $result;
    }
}
