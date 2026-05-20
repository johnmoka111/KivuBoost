-- ============================================================
-- MIGRATION : Système Multidevise (USD/CDF) — KivuBoost
-- ============================================================
-- Exécuter ce script UNE SEULE FOIS sur votre base de données
-- bukavuboost via phpMyAdmin ou en ligne de commande.
-- ============================================================

USE `bukavuboost`;

-- -------------------------------------------------------
-- ÉTAPE 1 : Mettre à jour la table `users`
-- Renommer balance -> wallet_usd et ajouter wallet_cdf
-- -------------------------------------------------------
-- Vérifier si la colonne balance existe avant de la renommer
SET @col_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'bukavuboost'
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'balance'
);

-- Renommer balance en wallet_usd si elle existe
ALTER TABLE `users`
    CHANGE COLUMN IF EXISTS `balance` `wallet_usd` DECIMAL(10,2) NOT NULL DEFAULT 0.00;

-- Ajouter wallet_cdf si elle n'existe pas encore
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `wallet_cdf` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `wallet_usd`;

-- -------------------------------------------------------
-- ÉTAPE 2 : Ajouter la colonne devise à la table `recharges`
-- -------------------------------------------------------
ALTER TABLE `recharges`
    ADD COLUMN IF NOT EXISTS `currency` ENUM('USD','CDF') NOT NULL DEFAULT 'USD' AFTER `amount`;

-- -------------------------------------------------------
-- ÉTAPE 3 : Ajouter la colonne devise à la table `orders`
-- (pour savoir dans quelle devise la commande a été payée)
-- -------------------------------------------------------
ALTER TABLE `orders`
    ADD COLUMN IF NOT EXISTS `currency` ENUM('USD','CDF') NOT NULL DEFAULT 'USD' AFTER `cost`;

-- -------------------------------------------------------
-- ÉTAPE 4 : Insérer/Mettre à jour le taux de change dans settings
-- -------------------------------------------------------
INSERT INTO `settings` (`cfg_key`, `cfg_value`, `cfg_group`, `description`) VALUES
('usd_rate_cdf', '2850', 'finance', 'Taux de change : 1 USD = X CDF (modifiable par admin)')
ON DUPLICATE KEY UPDATE `cfg_value` = VALUES(`cfg_value`), `cfg_group` = 'finance', `description` = VALUES(`description`);
