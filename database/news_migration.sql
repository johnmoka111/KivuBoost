-- --------------------------------------------------------
-- KivuBoost — Migration : Table `news`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `news` (
  `id`         INT(11)          NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(255)     NOT NULL,
  `slug`       VARCHAR(255)     NOT NULL,
  `image_path` VARCHAR(255)     NULL DEFAULT NULL,
  `summary`    TEXT             NOT NULL,
  `content`    LONGTEXT         NOT NULL,
  `status`     ENUM('brouillon','publie') NOT NULL DEFAULT 'publie',
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_news_slug` (`slug`),
  KEY `idx_news_status_created` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
