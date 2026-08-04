-- Narrador Studio database schema
-- Reproducible schema for the current application state.

CREATE TABLE IF NOT EXISTS projects (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    archived_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_projects_uuid (uuid),
    KEY idx_projects_status (status),
    KEY idx_projects_created_at (created_at),
    CONSTRAINT chk_projects_status CHECK (status IN ('draft', 'active', 'archived'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
