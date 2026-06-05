-- install.sql — SenMarket
-- Exécuter : mysql -u root -p senmarket < install.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── PRODUITS ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `products` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`           VARCHAR(150) NOT NULL,
  `category`       ENUM('Artisanat','Alimentaire','Cosmétiques') NOT NULL,
  `price`          INT UNSIGNED NOT NULL COMMENT 'Prix en FCFA',
  `original_price` INT UNSIGNED DEFAULT NULL,
  `stock`          SMALLINT UNSIGNED NOT NULL DEFAULT 10,
  `emoji`          VARCHAR(10) NOT NULL DEFAULT '🛍️',
  `artisan`        VARCHAR(100) NOT NULL,
  `description`    TEXT,
  `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── COMMANDES ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `orders` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `reference`        VARCHAR(20) NOT NULL UNIQUE,
  `status`           ENUM('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  `payment_method`   ENUM('orange_money','wave','card','cash') NOT NULL,
  -- Informations client
  `client_name`      VARCHAR(100) NOT NULL,
  `client_email`     VARCHAR(150) NOT NULL,
  `client_phone`     VARCHAR(25)  NOT NULL,
  -- Livraison
  `shipping_address` VARCHAR(255) NOT NULL,
  `shipping_city`    VARCHAR(100) NOT NULL,
  `shipping_country` VARCHAR(100) NOT NULL DEFAULT 'Sénégal',
  -- Totaux
  `subtotal`         INT UNSIGNED NOT NULL,
  `shipping_cost`    INT UNSIGNED NOT NULL DEFAULT 0,
  `total`            INT UNSIGNED NOT NULL,
  `notes`            TEXT,
  `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── LIGNES DE COMMANDE ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `order_items` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id`     INT UNSIGNED NOT NULL,
  `product_id`   INT UNSIGNED NOT NULL,
  `product_name` VARCHAR(150) NOT NULL COMMENT 'Snapshot au moment de l\'achat',
  `unit_price`   INT UNSIGNED NOT NULL,
  `quantity`     SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `subtotal`     INT UNSIGNED NOT NULL,
  FOREIGN KEY (`order_id`)   REFERENCES `orders`(`id`)   ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ── DONNÉES DE TEST ───────────────────────────────────────────
INSERT INTO `products` (`name`,`category`,`price`,`original_price`,`stock`,`emoji`,`artisan`,`description`) VALUES
('Panier Tressé Traditionnel','Artisanat',   15000,25000,8, '🧺','Aminata D.','Panier en osier tressé à la main selon les traditions sénégalaises.'),
('Bijoux en Perles Artisanaux','Artisanat',   8500, NULL, 15,'💎','Fatou S.',  'Collier et bracelet en perles multicolores fait main.'),
('Beurre de Karité Pur',      'Cosmétiques',12000,16000,20,'🧴','Mariama B.','100% naturel, hydrate et nourrit la peau en profondeur.'),
('Tissu Wax Premium',         'Artisanat',  18000, NULL, 5, '🎨','Oumar T.',  'Tissu wax authentique aux motifs traditionnels.'),
('Épices Thiéboudienne',      'Alimentaire', 4500, NULL, 50,'🌶️','Rokhaya N.','Mélange d\'épices authentiques pour le thiéboudienne.'),
('Huile d\'Argan Artisanale', 'Cosmétiques', 9800,13000,12,'🫙','Aissatou D.','Huile d\'argan pure, pressée à froid.'),
('Chapeau Tressé Baobab',     'Artisanat',   7200, NULL, 7, '👒','Ibrahima S.','Chapeau de paille tressé à la main, style africain.'),
('Mélange d\'Épices Yassa',   'Alimentaire', 3200, NULL, 30,'🌿','Ndèye F.',  'Mélange d\'épices traditionnel pour le poulet yassa.'),
('Savon au Lait de Karité',   'Cosmétiques', 2800, NULL, 40,'🧼','Coumba M.','Savon artisanal doux, enrichi au beurre de karité.'),
('Bogolan Tissu Authentique', 'Artisanat',  22000, NULL, 4, '🎭','Moussa K.', 'Tissu bogolan peint à la main avec des pigments naturels.'),
('Jus de Bissap Premium',     'Alimentaire', 3500, NULL, 25,'🥤','Soda T.',   'Jus de bissap naturel sans conservateurs.'),
('Collier Cauris Traditionnel','Artisanat',  6500, 9000, 10,'📿','Khadija B.','Collier de cauris naturels monté à la main.');