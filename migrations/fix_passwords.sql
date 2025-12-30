-- Fix Password Hashes
-- Run this to update passwords for existing users

USE helpdesk_db;

-- Update admin password (admin123)
UPDATE users SET password = '$2y$12$8Wos13KhnYKoWOttGDI28OSaxxrS/oAIhtcczMKtMY5rm0EytQF7i' WHERE email = 'admin@jpbdselangor.gov.my';

-- Update test user password (user123)
UPDATE users SET password = '$2y$12$0ploemIfcfDq/W/calvyx.oQLVsv12pG8sNr2U4Ci3EDgWroPKJyK' WHERE email = 'ahmad.user@jpbdselangor.gov.my';

-- Verify updates
SELECT id, nama_penuh, email, role, status FROM users;
