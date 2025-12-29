-- Migration: Add tingkat column to users table
-- Run this if you already have an existing database

USE helpdesk_db;

-- Add tingkat column to users table
ALTER TABLE users
ADD COLUMN tingkat VARCHAR(50) AFTER no_sambungan;
