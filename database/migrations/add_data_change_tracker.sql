-- ────────────────────────────────────────────────────
-- Smart Real-time: Data Change Tracker Table
-- Lightweight table that records the last-modified
-- timestamp per data category. The check_updates
-- endpoint reads this with a single SELECT to decide
-- whether the client needs to re-fetch data.
-- ────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `data_change_tracker` (
    `category`    VARCHAR(50)  NOT NULL,
    `updated_at`  DATETIME(3)  NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    PRIMARY KEY (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed initial categories
INSERT INTO `data_change_tracker` (`category`, `updated_at`) VALUES
    ('equipment',    NOW(3)),
    ('employees',    NOW(3)),
    ('maintenance',  NOW(3)),
    ('organization', NOW(3)),
    ('software',     NOW(3)),
    ('settings',     NOW(3)),
    ('accounts',     NOW(3))
ON DUPLICATE KEY UPDATE `updated_at` = VALUES(`updated_at`);
