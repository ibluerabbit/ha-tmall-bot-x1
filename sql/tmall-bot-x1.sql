CREATE TABLE  `devices` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `user_id` VARCHAR(80) NOT NULL,
  `deviceId` VARCHAR(255) NOT NULL, 
  `deviceName` VARCHAR(255) NOT NULL DEFAULT '', 
  `deviceType` VARCHAR(255) NOT NULL DEFAULT '', 
  `zone` VARCHAR( 80 ) NOT NULL DEFAULT '',
  `brand` VARCHAR( 80 ),
  `model` VARCHAR( 80 ),
  `icon` VARCHAR( 255 ) NOT NULL DEFAULT '',
  `properties` TEXT NOT NULL, 
  `actions` TEXT NOT NULL, 
  `extensions` TEXT NOT NULL 
);
