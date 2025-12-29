-- Migration: Add Unit Aset processing columns
-- Date: 2025-12-29
-- Description: Adds columns for tracking Unit Aset processing

USE helpdesk_db;

-- Add columns for Unit Aset processing tracking
ALTER TABLE complaints
ADD COLUMN unit_aset_processed_by INT DEFAULT NULL COMMENT 'User ID who processed in Unit Aset';

ALTER TABLE complaints
ADD COLUMN unit_aset_processed_at DATETIME DEFAULT NULL COMMENT 'When Unit Aset processed';

ALTER TABLE complaints
ADD COLUMN approval_officer_id INT DEFAULT NULL COMMENT 'Approval officer for this complaint';

-- Add foreign key constraints
ALTER TABLE complaints
ADD CONSTRAINT fk_unit_aset_processed_by FOREIGN KEY (unit_aset_processed_by) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE complaints
ADD CONSTRAINT fk_approval_officer FOREIGN KEY (approval_officer_id) REFERENCES users(id) ON DELETE SET NULL;
