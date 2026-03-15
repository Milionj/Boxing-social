ALTER TABLE posts
    ADD COLUMN media_type VARCHAR(20) NOT NULL DEFAULT 'image' AFTER image_path,
    ADD COLUMN media_size VARCHAR(20) NOT NULL DEFAULT 'standard' AFTER media_type;
