<?php

header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];

// 1. Search Users
$users = db('users')
    ->where('name', 'LIKE', "%$q%")
    ->orWhere('username', 'LIKE', "%$q%")
    ->orWhere('email', 'LIKE', "%$q%")
    ->limit(10)
    ->get()
    ->map(fn($x)=>(array)$x)
    ->all();

foreach ($users as $u) {
    $results[] = [
        'title' => $u['name'],
        'subtitle' => "@" . $u['username'] . " • " . $u['email'],
        'url' => url('/view/user/' . $u['id']),
        'edit_url' => url('/edit/user/' . $u['id']),
        'parent' => 'Users'
    ];
}

// 2. Search Settings (Optional - only keys that match)
$settings = db('settings')
    ->where('key', 'LIKE', "%$q%")
    ->limit(5)
    ->get()
    ->map(fn($x)=>(array)$x)
    ->all();

foreach ($settings as $s) {
    $results[] = [
        'title' => str_replace('_', ' ', $s['key']),
        'subtitle' => 'System Setting',
        'url' => url('/settings'),
        'parent' => 'Settings'
    ];
}

echo json_encode($results);
exit;
