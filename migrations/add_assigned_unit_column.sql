-- Migration: Add assigned_unit column to complaints table
-- This column tracks whether a complaint is assigned to Unit IT or Unit Pentadbiran

ALTER TABLE complaints
ADD COLUMN assigned_unit ENUM('unit_it', 'unit_pentadbiran') DEFAULT NULL COMMENT 'Which unit is assigned (Unit IT or Unit Pentadbiran)'
AFTER unit_it_officer_id;

-- Add index for better query performance
CREATE INDEX idx_assigned_unit ON complaints(assigned_unit);
