-- ============================================================
-- KivuBoost — Migration : Journalisation des Emails (SMTP)
-- ============================================================

CREATE TABLE IF NOT EXISTS `email_logs` (
  `id`            INT          NOT NULL AUTO_INCREMENT,
  `recipient`     VARCHAR(255) NOT NULL,
  `subject`       VARCHAR(255) NOT NULL,
  `template`      VARCHAR(100) NOT NULL,
  `status`        ENUM('sent', 'failed') NOT NULL,
  `error_message` TEXT         NULL DEFAULT NULL,
  `sent_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email_recipient` (`recipient`),
  KEY `idx_email_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
