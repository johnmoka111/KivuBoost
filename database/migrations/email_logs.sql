-- ============================================================
-- KivuBoost — Migration : Table de logs des e-mails
-- Fichier  : database/migrations/email_logs.sql
-- Usage    : Importer dans phpMyAdmin → base 'bukavuboost'
-- ============================================================

CREATE TABLE IF NOT EXISTS `email_logs` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `recipient`     VARCHAR(255)     NOT NULL COMMENT 'Adresse e-mail du destinataire',
    `subject`       VARCHAR(500)     NOT NULL COMMENT 'Sujet du message envoyé',
    `template`      VARCHAR(100)     NOT NULL COMMENT 'Nom du gabarit utilisé (sans .php)',
    `status`        ENUM('sent', 'failed') NOT NULL DEFAULT 'failed' COMMENT 'Résultat de la tentative d''envoi',
    `error_message` TEXT             NULL     COMMENT 'Message d''erreur PHPMailer si envoi échoué',
    `sent_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Horodatage de la tentative d''envoi',
    PRIMARY KEY (`id`),
    INDEX `idx_recipient` (`recipient`),
    INDEX `idx_status`    (`status`),
    INDEX `idx_template`  (`template`),
    INDEX `idx_sent_at`   (`sent_at`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Journal de toutes les tentatives d''envoi d''e-mails transactionnels KivuBoost';
