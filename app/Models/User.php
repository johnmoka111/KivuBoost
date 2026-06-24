<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $username, string $email, string $passwordHash, string $role = 'user'): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password, role, balance) VALUES (?, ?, ?, ?, 0.00)'
        );
        $stmt->execute([$username, $email, $passwordHash, $role]);
        return (int)$this->db->lastInsertId();
    }

    public function updateProfile(int $id, string $username, string $email): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET username = ?, email = ? WHERE id = ?'
        );
        return $stmt->execute([$username, $email, $id]);
    }

    public function updatePreferences(int $id, string $currency, string $language, string $timezone, string $theme): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET currency_pref = ?, language_pref = ?, timezone_pref = ?, theme_pref = ? WHERE id = ?'
        );
        return $stmt->execute([$currency, $language, $timezone, $theme, $id]);
    }

    public function updateApiKey(int $id, string $apiKey): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET api_key = ? WHERE id = ?'
        );
        return $stmt->execute([$apiKey, $id]);
    }

    public function updatePassword(int $id, string $passwordHash): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET password = ? WHERE id = ?'
        );
        return $stmt->execute([$passwordHash, $id]);
    }

    public function updateAvatar(int $id, string $avatar): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET avatar = ? WHERE id = ?'
        );
        return $stmt->execute([$avatar, $id]);
    }

    public function debitBalance(int $id, float $amount): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET balance = balance - ? WHERE id = ? AND balance >= ?'
        );
        return $stmt->execute([$amount, $id, $amount]);
    }

    public function creditBalance(int $id, float $amount): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET balance = balance + ? WHERE id = ?'
        );
        return $stmt->execute([$amount, $id]);
    }

    public function adjustBalance(int $id, float $amount): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET balance = balance + ? WHERE id = ?'
        );
        return $stmt->execute([$amount, $id]);
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM users ORDER BY username')->fetchAll();
    }
}
