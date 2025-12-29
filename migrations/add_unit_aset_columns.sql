-- Migration: Add Unit Aset processing columns
-- Date: 2025-12-29
-- Description: Adds columns for tracking Unit Aset processing

USE helpdesk_db;

-- Add columns if they don't exist
-- Unit Aset processing tracking
ALTER TABLE complaints
ADD COLUMN IF NOT EXISTS unit_aset_processed_by INT DEFAULT NULL COMMENT 'User ID who processed in Unit Aset',
ADD COLUMN IF NOT EXISTS unit_aset_processed_at DATETIME DEFAULT NULL COMMENT 'When Unit Aset processed',
ADD COLUMN IF NOT EXISTS approval_officer_id INT DEFAULT NULL COMMENT 'Approval officer for this complaint';

-- Add foreign keys if they don't exist
ALTER TABLE complaints
ADD CONSTRAINT IF NOT EXISTS fk_unit_aset_processed_by FOREIGN KEY (unit_aset_processed_by) REFERENCES users(id) ON DELETE SET NULL,
ADD CONSTRAINT IF NOT EXISTS fk_approval_officer FOREIGN KEY (approval_officer_id) REFERENCES users(id) ON DELETE SET NULL;
