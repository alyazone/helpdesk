-- Migration: Add password reset token fields to users table
-- Date: 2025-12-07
-- Description: Adds reset_token and reset_token_expires fields for forgot password functionality

USE helpdesk_db;

-- Add reset token fields to users table if they don't exist
ALTER TABLE users
ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS reset_token_expires DATETIME DEFAULT NULL,
ADD INDEX IF NOT EXISTS idx_reset_token (reset_token);
