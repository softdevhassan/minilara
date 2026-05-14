<?php
/**
 * Master Database Schema & Relationships
 * Single Source of Truth - Sync'd with Live Environment
 */

return [
    "users" => [
        "sql" => "CREATE TABLE IF NOT EXISTS `users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `email` varchar(255) NOT NULL,
          `phone` varchar(255) NOT NULL,
          `username` varchar(100) NOT NULL,
          `password` varchar(255) NOT NULL,
          `image` varchar(255) NOT NULL DEFAULT '/images/default-avatar.webp',
          `details` longtext DEFAULT NULL,
          `allowed_routes` longtext DEFAULT NULL,
          `locked` tinyint(4) NOT NULL DEFAULT 0,
          `color_scheme` text DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT current_timestamp(),
          `last_updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          `last_updated_by` int(11) NOT NULL DEFAULT 1,
          PRIMARY KEY (`id`),
          UNIQUE KEY `username` (`username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;"
    ],

    "settings" => [
        "sql" => "CREATE TABLE IF NOT EXISTS `settings` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `key` varchar(255) NOT NULL,
          `value1` text DEFAULT NULL,
          `value2` text DEFAULT NULL,
          `value3` text DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT current_timestamp(),
          `last_updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          `last_updated_by` int(11) NOT NULL DEFAULT 1,
          PRIMARY KEY (`id`),
          UNIQUE KEY `key` (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;"
    ]
];
