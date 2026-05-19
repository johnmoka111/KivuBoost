-- ============================================================
-- BukavuBoost SMM Panel — Script SQL Complet (Multi-API)
-- Version: 2.0 | Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS `bukavuboost`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `bukavuboost`;

-- -------------------------------------------------------
-- TABLE: users
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT(11)       NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(50)   NOT NULL,
  `email`      VARCHAR(100)  NOT NULL,
  `password`   VARCHAR(255)  NOT NULL,
  `balance`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `role`       ENUM('user','admin','superadmin') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email`    (`email`),
  UNIQUE KEY `uq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- TABLE: providers (Multi-API)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `providers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,          -- Ex: 'SmmFollows', 'Easy SMM Panel'
    `api_url` VARCHAR(255) NOT NULL,       -- Ex: 'https://smmfollows.com'
    `api_key` VARCHAR(255) NOT NULL,       -- La clé API secrète du grossiste
    `status` INT DEFAULT 1                 -- 1 = Actif, 0 = Inactif / Maintenance
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- TABLE: services (Dynamic multi-api pricing)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `provider_id` INT,                     -- Clé étrangère reliée à la table providers
    `external_service_id` INT,             -- L'ID exact du service CHEZ le grossiste
    `category` VARCHAR(100) NOT NULL,       -- Catégorie (ex: TikTok Followers)
    `name` VARCHAR(255) NOT NULL,           -- Nom affiché au client à Bukavu
    `buying_price` DECIMAL(10,4) NOT NULL,  -- Le prix d'achat réel chez le fournisseur (ex: 0.1200)
    `selling_price` DECIMAL(10,4) NOT NULL, -- Le prix que vous affichez à Bukavu (ex: 1.5000)
    `min_quantity` INT NOT NULL,
    `max_quantity` INT NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`provider_id`) REFERENCES `providers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- TABLE: orders
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id`                INT(11)       NOT NULL AUTO_INCREMENT,
  `user_id`           INT(11)       NOT NULL,
  `service_id`        INT(11)       NOT NULL,
  `link`              VARCHAR(500)  NOT NULL,
  `quantity`          INT(11)       NOT NULL,
  `cost`              DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
  `external_order_id` VARCHAR(50)   DEFAULT NULL,
  `status`            ENUM('Pending','Processing','Completed','Canceled','Partial') NOT NULL DEFAULT 'Pending',
  `created_at`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_order_user`    (`user_id`),
  KEY `fk_order_service` (`service_id`),
  CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- TABLE: recharges
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `recharges` (
  `id`             INT(11)       NOT NULL AUTO_INCREMENT,
  `user_id`        INT(11)       NOT NULL,
  `amount`         DECIMAL(10,2) NOT NULL,
  `network`        ENUM('M-Pesa','Airtel Money','Orange Money','Vodacom') NOT NULL,
  `transaction_id` VARCHAR(100)  NOT NULL,
  `status`         ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `notes`          TEXT          DEFAULT NULL,
  `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_recharge_user` (`user_id`),
  CONSTRAINT `fk_recharge_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- TABLE: settings (configuration centrale)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `cfg_key`     VARCHAR(100) NOT NULL,
  `cfg_value`   TEXT         DEFAULT NULL,
  `cfg_group`   VARCHAR(50)  NOT NULL DEFAULT 'general',
  `description` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cfg_key` (`cfg_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- SEED: Paramètres par défaut
-- -------------------------------------------------------
INSERT INTO `settings` (`cfg_key`, `cfg_value`, `cfg_group`, `description`) VALUES
('site_name',          'BukavuBoost',                  'general',  'Nom du site'),
('markup_percentage',  '20',                            'general',  'Marge sur prix fournisseur (%)'),
('mpesa_number',       '+243XXXXXXXXX',                 'payment',  'Numéro M-Pesa de réception'),
('airtel_number',      '+243XXXXXXXXX',                 'payment',  'Numéro Airtel Money de réception'),
('orange_number',      '+243XXXXXXXXX',                 'payment',  'Numéro Orange Money de réception'),
('vodacom_number',     '+243XXXXXXXXX',                 'payment',  'Numéro Vodacom de réception'),
('pawapay_enabled',    '0',                             'pawapay',  'Activer PawaPay (0/1)'),
('pawapay_api_key',    '',                              'pawapay',  'Clé API PawaPay'),
('pawapay_secret',     '',                              'pawapay',  'Secret PawaPay'),
('visapay_enabled',    '0',                             'visapay',  'Activer VisaPay (0/1)'),
('visapay_api_key',    '',                              'visapay',  'Clé API VisaPay'),
('visapay_secret',     '',                              'visapay',  'Secret VisaPay')
ON DUPLICATE KEY UPDATE `cfg_value` = VALUES(`cfg_value`);

-- -------------------------------------------------------
-- SEED: Fournisseurs exemples
-- -------------------------------------------------------
INSERT INTO `providers` (`name`, `api_url`, `api_key`, `status`) 
VALUES ('SmmFollows', 'https://smmfollows.com/api/v2', 'CLE_SECRETE_SMM_FOLLOWS', 1);

-- -------------------------------------------------------
-- SEED: Services exemples
-- -------------------------------------------------------
INSERT INTO `services` (`provider_id`, `external_service_id`, `category`, `name`, `buying_price`, `selling_price`, `min_quantity`, `max_quantity`) 
VALUES (1, 482, 'TikTok - Followers', 'TikTok Abonnés Réels (Garantis 30j)', 0.1500, 2.5000, 100, 10000);

-- -------------------------------------------------------
-- SEED: Compte SuperAdmin par défaut
-- Mot de passe: Admin@2024 (doit être configuré via setup.php)
-- -------------------------------------------------------
INSERT INTO `users` (`username`, `email`, `password`, `balance`, `role`) VALUES
('superadmin', 'admin@bukavuboost.cd', 'RUN_SETUP_PHP', 0.00, 'superadmin')
ON DUPLICATE KEY UPDATE `role` = 'superadmin';
