ALTER TABLE posts
    ADD COLUMN post_type VARCHAR(30) NOT NULL DEFAULT 'publication' AFTER user_id,
    ADD COLUMN scheduled_at DATETIME NULL AFTER location;
