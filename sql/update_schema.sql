USE car_sweepstakes;

CREATE TABLE IF NOT EXISTS settings (
    meta_key VARCHAR(50) PRIMARY KEY,
    meta_value TEXT
);

INSERT IGNORE INTO settings (meta_key, meta_value) VALUES 
('draw_date', '2025-12-31 23:59:59'),
('winner_id', NULL),
('draw_completed', '0');
