-- STEP 1: Add notification_preferences column
ALTER TABLE `data_pengguna` 
ADD COLUMN `notification_preferences` JSON NULL DEFAULT NULL 
AFTER `profile_image`;

-- STEP 2: Add app_language column  
ALTER TABLE `data_pengguna`
ADD COLUMN `app_language` VARCHAR(10) NOT NULL DEFAULT 'id' 
AFTER `notification_preferences`;

-- STEP 3: Add app_theme column
ALTER TABLE `data_pengguna`
ADD COLUMN `app_theme` VARCHAR(10) NOT NULL DEFAULT 'auto' 
AFTER `app_language`;

-- STEP 4: Add app_distance_unit column
ALTER TABLE `data_pengguna`
ADD COLUMN `app_distance_unit` VARCHAR(10) NOT NULL DEFAULT 'km' 
AFTER `app_theme`;

-- STEP 5: Add app_auto_location column
ALTER TABLE `data_pengguna`
ADD COLUMN `app_auto_location` TINYINT(1) NOT NULL DEFAULT 1 
AFTER `app_distance_unit`;

-- STEP 6: Add app_manual_location column
ALTER TABLE `data_pengguna`
ADD COLUMN `app_manual_location` VARCHAR(255) NULL DEFAULT NULL 
AFTER `app_auto_location`;

-- STEP 7: Set default notification preferences for existing users
UPDATE `data_pengguna` 
SET `notification_preferences` = '{"email_notifications":true,"booking_reminders":true,"profile_updates":true,"password_changes":true}'
WHERE `notification_preferences` IS NULL;
