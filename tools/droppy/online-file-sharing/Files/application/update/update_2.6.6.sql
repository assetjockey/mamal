-- Add index to `droppy_uploads` table
CREATE INDEX idx_upload_id ON droppy_uploads(upload_id);
CREATE INDEX idx_secret_code ON droppy_uploads(secret_code);
CREATE INDEX idx_status ON droppy_uploads(status);

-- Add index to `droppy_downloads` table
CREATE INDEX idx_download_id ON droppy_downloads(download_id);
CREATE INDEX idx_email ON droppy_downloads(email);

-- Add index to `droppy_files` table
CREATE INDEX idx_upload_id ON droppy_files(upload_id);
CREATE INDEX idx_secret_code ON droppy_files(secret_code);

-- Add index to `droppy_receivers` table
CREATE INDEX idx_upload_id ON droppy_receivers(upload_id);
CREATE INDEX idx_email ON droppy_receivers(email);
CREATE INDEX idx_private_id ON droppy_receivers(private_id);

-- Add index to `droppy_pages` table
CREATE INDEX idx_type ON droppy_pages(type);
CREATE INDEX idx_lang ON droppy_pages(lang);