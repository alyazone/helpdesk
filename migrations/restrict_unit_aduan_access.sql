-- Migration: Remove Unit Aduan Dalaman access from Azri
-- Description: Update Azri's primary role and ensure only Norhayati has access to Unit Aduan Dalaman
-- Date: 2026-01-09

-- Update Azri's primary role in users table from unit_aduan_dalaman to unit_aset
UPDATE users
SET role = 'unit_aset'
WHERE email = 'azri@jpbdselangor.gov.my';

-- Ensure Norhayati's primary role is admin (so she can access everything)
UPDATE users
SET role = 'admin'
WHERE email = 'norhayati@jpbdselangor.gov.my';

-- Double-check: Remove any unit_aduan_dalaman role from azri in user_roles table (just in case)
DELETE FROM user_roles
WHERE user_id = (SELECT id FROM users WHERE email = 'azri@jpbdselangor.gov.my')
AND role_name = 'unit_aduan_dalaman';

-- Verify only Norhayati has unit_aduan_dalaman in user_roles
-- This query will show who has unit_aduan_dalaman access
-- Expected result: Only norhayati@jpbdselangor.gov.my

-- Migration complete
