-- ============================================================
-- KivuBoost — Migration : Support Client Dynamique
-- ============================================================

CREATE TABLE IF NOT EXISTS `support_agents` (
  `id`               INT          NOT NULL AUTO_INCREMENT,
  `name`             VARCHAR(100) NOT NULL,
  `city`             VARCHAR(100) NOT NULL,
  `photo_path`       VARCHAR(255) NULL     DEFAULT NULL,
  `whatsapp_number`  VARCHAR(20)  NOT NULL,
  `is_active`        TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insère les clés de support dans la table settings existante
INSERT IGNORE INTO `settings` (`cfg_key`, `cfg_value`, `cfg_group`, `description`) VALUES
  ('main_whatsapp',  '',  'support', 'Numéro WhatsApp principal (format international sans +)'),
  ('facebook_url',   '',  'support', 'URL de la page Facebook de KivuBoost'),
  ('instagram_url',  '',  'support', 'URL du profil Instagram de KivuBoost'),
  ('support_page_enabled', '1', 'support', 'Activer/désactiver la page publique de l\'équipe support');
