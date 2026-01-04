-- Wallet Methods Table
-- Stores user payment methods (NO payment processing, storage only)

CREATE TABLE `wallet_methods` (
  `id_wallet` int(11) NOT NULL AUTO_INCREMENT,
  `id_pengguna` int(11) NOT NULL,
  `type` enum('bank','ewallet','paypal') NOT NULL,
  `provider_name` varchar(50) NOT NULL COMMENT 'BCA, Mandiri, DANA, OVO, PayPal, etc',
  `account_identifier` varchar(255) NOT NULL COMMENT 'Masked account number (e.g., ****1234)',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_wallet`),
  KEY `fk_wallet_pengguna` (`id_pengguna`),
  KEY `idx_default` (`id_pengguna`, `is_default`),
  CONSTRAINT `fk_wallet_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `data_pengguna` (`id_pengguna`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Index for faster lookups
CREATE INDEX idx_user_default ON wallet_methods(id_pengguna, is_default);
