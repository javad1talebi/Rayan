USE `asterisk`;

-- جدول تنظیمات نظرسنجی
CREATE TABLE IF NOT EXISTS `survey_property` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `survey_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `audio_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'inactive',
  `max_invalid` int(11) DEFAULT '3',
  `queue` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_score_record` enum('disabled','1','2') COLLATE utf8mb4_unicode_ci DEFAULT 'disabled',
  `max_score_record` enum('disabled','3','4','5') COLLATE utf8mb4_unicode_ci DEFAULT 'disabled',
  `operator_identity` enum('disabled','name','number') COLLATE utf8mb4_unicode_ci DEFAULT 'disabled',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول ثبت نتایج نظرسنجی
CREATE TABLE IF NOT EXISTS `survey` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_time` datetime NOT NULL,
  `survey_value` int(11) NOT NULL,
  `agent_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caller_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caller_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uniqueid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `survey_location` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complaint_record_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operator_identity` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول کاربران سامانه نظرسنجی
CREATE TABLE IF NOT EXISTS `survey_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_persian_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_persian_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_persian_ci DEFAULT 'user',
  `status` enum('active','inactive') COLLATE utf8mb4_persian_ci NOT NULL DEFAULT 'active',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- افزودن کاربر مدیر پیش‌فرض
INSERT IGNORE INTO survey_users (username, password, full_name, role, status, is_active)
VALUES ('admin', MD5('1'), 'admin', 'admin', 'active', 1);

-- جدول لایسنس
CREATE TABLE IF NOT EXISTS `license` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `license_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
