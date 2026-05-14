<?php

/**
 * Master Database Seeder
 * Populates the database with essential initial data.
 * ---------------------------------------------------------------------
 * @var \PDO $db - Injected by \App\DevTool
 */

// 1. Default Admin (Password: admin)
$adminPassword = '$2y$10$RWIBxtIgYUl.AUbhP4P9HexcaS0qihwxMaLpcqmvQRo0I3cZCBk7q';
$db->prepare("INSERT IGNORE INTO `users` (`id`, `name`, `email`, `phone`, `username`, `password`, `allowed_routes`) VALUES (?, ?, ?, ?, ?, ?, ?)")
    ->execute([1, 'Admin', 'admin@minilara.com', '03000000000', 'admin', $adminPassword, 'ALL']);

// 2. Default Settings (Pull from ENV if available)
$settings = [
    ['APP_NAME', $_ENV['APP_NAME'] ?? 'Mini Lara'],
    ['APP_SHORT_NAME', $_ENV['APP_SHORT_NAME'] ?? 'ML'],
    ['APP_FULL_NAME', $_ENV['APP_FULL_NAME'] ?? 'Mini Lara Business Management'],
    ['APP_TAGLINE', $_ENV['APP_TAGLINE'] ?? 'Clean & Scalable Management System'],
    ['APP_USE_DYNAMIC_FAVICON', '1'],
    ['APP_ICON_TEXT', $_ENV['APP_SHORT_NAME'] ?? 'ML'],
    ['APP_ENABLE_ALERTS', '1'],
    ['APP_SHOW_BREADCRUMBS', '1'],
    ['TITLE_SEPERATOR', ' | '],
    ['APP_EMAIL', $_ENV['APP_EMAIL'] ?? 'softdevhassan.biz@gmail.com'],
    ['APP_PHONE1', $_ENV['APP_PHONE'] ?? '03397133082'],
    ['APP_PHONE2', ''],
    ['APP_ADDRESS', 'Address Here'],
    ['PRINT_REPORT_MARGIN_TOP', '1.5in'],
    ['PRINT_REPORT_PADDING_TOP', '1.5in'],
    ['DEVELOPER_NAME', $_ENV['DEVELOPER_NAME'] ?? 'Hassan Ali'],
    ['DEVELOPER_URL', $_ENV['DEVELOPER_URL'] ?? 'https://linktr.ee/softdevhassan'],
    ['SYSTEM_DEFAULT_COLORS', '{"name":"Enterprise White (Ocean Blue)","tp":"#0f172a","ts":"#4b5563","bp":"#ffffff","bs":"#f9fafb","sb":"#2563eb","sl":"#eff6ff","sh":"#ffffff","sa":"#ffffff","st":"#2563eb","accent":"#2563eb","accent_light":"#60a5fa","accent_dark":"#1d4ed8"}'],
];
foreach ($settings as $s) {
    $db->prepare("INSERT IGNORE INTO `settings` (`key`, `value1`) VALUES (?, ?)")->execute($s);
}

echo " - Success: Industrial baseline data seeded.\n";
