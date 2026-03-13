CREATE TABLE IF NOT EXISTS user_settings (
    user_id INT NOT NULL PRIMARY KEY,
    theme VARCHAR(20) NOT NULL DEFAULT 'systeme',
    language VARCHAR(20) NOT NULL DEFAULT 'francais',
    parental_controls TINYINT(1) NOT NULL DEFAULT 0,
    notifications_enabled TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_settings_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
);
