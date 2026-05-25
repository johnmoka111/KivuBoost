<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class News
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getPublished(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, title, slug, image_path, summary, created_at
             FROM news
             WHERE status = 'publie'
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM news WHERE slug = :slug AND status = 'publie' LIMIT 1"
        );
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function insert(
        string $title,
        string $slug,
        string $summary,
        string $content,
        ?string $imagePath,
        string $status = 'publie'
    ): int {
        $stmt = $this->db->prepare(
            "INSERT INTO news (title, slug, image_path, summary, content, status)
             VALUES (:title, :slug, :image_path, :summary, :content, :status)"
        );
        $stmt->execute([
            ':title'      => $title,
            ':slug'       => $slug,
            ':image_path' => $imagePath,
            ':summary'    => $summary,
            ':content'    => $content,
            ':status'     => $status,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function slugExists(string $slug): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM news WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        return (bool) $stmt->fetch();
    }
}
