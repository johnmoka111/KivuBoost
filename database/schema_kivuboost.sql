-- --------------------------------------------------------
-- FONDATION DE LA BASE DE DONNÉES - KIVUBOOST SMM
-- --------------------------------------------------------

-- 1. Mise à jour de la table `users` existante
-- Remarque: Si les colonnes existent déjà, ces requêtes retourneront une erreur,
-- vous pouvez ignorer l'erreur si c'est le cas.
ALTER TABLE `users` 
ADD COLUMN `api_key` VARCHAR(64) UNIQUE DEFAULT NULL AFTER `password`,
ADD COLUMN `role` ENUM('client', 'admin') DEFAULT 'client' AFTER `api_key`;

-- Optionnel : Créer un index sur api_key pour accélérer les requêtes d'authentification API
CREATE INDEX `idx_api_key` ON `users` (`api_key`);

-- --------------------------------------------------------
-- 2. Création de la table `flux_recharges`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `flux_recharges` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `operateur` ENUM('M-Pesa', 'Airtel Money', 'Orange Money') NOT NULL,
  `jeton_sms` VARCHAR(100) NOT NULL,
  `montant` DECIMAL(10,2) NOT NULL,
  `statut` ENUM('en_attente', 'valide', 'refuse') DEFAULT 'en_attente',
  `date_soumission` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_jeton_sms` (`jeton_sms`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_flux_recharges_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Création de la table `commandes_locales`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `commandes_locales` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `service_id` INT(11) NOT NULL,
  `url_cible` VARCHAR(255) NOT NULL,
  `quantite_demandee` INT(11) NOT NULL,
  `id_fournisseur_externe` INT(11) DEFAULT NULL,
  `prix_achat_brut` DECIMAL(10,4) NOT NULL,
  `prix_facture_client` DECIMAL(10,4) NOT NULL,
  `statut` ENUM('reçu', 'traitement', 'livre', 'interrompu') DEFAULT 'reçu',
  `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_commandes_locales_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
  -- Note: service_id pourrait aussi avoir une Foreign Key si la table `services` existe
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
