-- Add profile_image column to data_pengguna table
-- This column will store the file path to the user's profile picture
-- Example: 'profile/user_123_1234567890.jpg'

ALTER TABLE `data_pengguna` 
ADD COLUMN `profile_image` VARCHAR(255) NULL DEFAULT NULL 
AFTER `noHp_pengguna`;
