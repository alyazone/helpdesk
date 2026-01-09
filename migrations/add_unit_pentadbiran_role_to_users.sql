-- Migration: Add unit_pentadbiran role to users
-- This script adds the unit_pentadbiran role to users who should have access

-- Option 1: Add unit_pentadbiran role to specific users by email
-- Replace 'user@jpbdselangor.gov.my' with the actual email address(es)
-- Uncomment the lines below and replace with actual email addresses

-- INSERT INTO user_roles (user_id, role_name)
-- SELECT id, 'unit_pentadbiran'
-- FROM users
-- WHERE email = 'user@jpbdselangor.gov.my'
-- AND NOT EXISTS (
--     SELECT 1 FROM user_roles
--     WHERE user_id = users.id AND role_name = 'unit_pentadbiran'
-- );

-- Option 2: Add unit_pentadbiran role to ALL users who have unit_it_sokongan role
-- (Useful if you want users to have both roles)
-- Uncomment the lines below if you want this behavior

-- INSERT INTO user_roles (user_id, role_name)
-- SELECT ur.user_id, 'unit_pentadbiran'
-- FROM user_roles ur
-- WHERE ur.role_name = 'unit_it_sokongan'
-- AND NOT EXISTS (
--     SELECT 1 FROM user_roles ur2
--     WHERE ur2.user_id = ur.user_id AND ur2.role_name = 'unit_pentadbiran'
-- );

-- Option 3: Check which users currently have which roles
-- Run this query to see existing role assignments:
-- SELECT u.id, u.nama_penuh, u.email, GROUP_CONCAT(ur.role_name) as roles
-- FROM users u
-- LEFT JOIN user_roles ur ON u.id = ur.user_id
-- GROUP BY u.id, u.nama_penuh, u.email
-- ORDER BY u.nama_penuh;

-- Example: Add unit_pentadbiran role to a specific user
-- Uncomment and modify the email address:
-- INSERT INTO user_roles (user_id, role_name)
-- SELECT id, 'unit_pentadbiran'
-- FROM users
-- WHERE email = 'your.email@jpbdselangor.gov.my'
-- AND NOT EXISTS (
--     SELECT 1 FROM user_roles
--     WHERE user_id = users.id AND role_name = 'unit_pentadbiran'
-- );
