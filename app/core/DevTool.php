<?php
namespace App;

/**
 * Mini Lara - Advanced Database DevTool (Simplified)
 */
class DevTool {
    public static function run() {
        if (($_ENV['DEV_DEBUG'] ?? 'false') !== 'true' || ($_GET['mode'] ?? '') !== 'db') return;

        $task = $_GET['task'] ?? 'status';
        self::renderHeader($task);

        try {
            switch ($task) {
                case 'wipe':    DB::wipe(); self::say("Database wiped.", "ok"); break;
                case 'seed':    DB::seed(); self::say("Data seeded.", "ok"); break;
                case 'migrate': DB::migrate(); self::say("Migrations applied.", "ok"); break;
                case 'reset':   DB::migrate(null, true); DB::seed(); self::say("Database reset.", "ok"); break;
                case 'status':
                default:        self::showStatus(); break;
            }
        } catch (\Exception $e) {
            self::say("Error: " . $e->getMessage(), "error");
        }

        self::renderFooter();
        exit;
    }

    private static function showStatus() {
        self::say("Current Database Registry Status", "header");
        $status = DB::getStatus();
        if (empty($status)) {
            self::say("Database is empty.", "warn");
        } else {
            echo "<table><thead><tr><th>Table Name</th><th>Row Count</th></tr></thead><tbody>";
            foreach ($status as $table => $count) {
                echo "<tr><td>$table</td><td style='font-weight:bold; color:#00ff41;'>$count</td></tr>";
            }
            echo "</tbody></table>";
        }
    }

    private static function say($msg, $type = 'info') {
        $colors = ['header' => '#00ff41', 'ok' => '#00ff41', 'warn' => '#ffcc00', 'error' => '#ff3333', 'info' => '#fff'];
        $color = $colors[$type] ?? '#fff';
        echo "<div style='color: $color; margin-top: 10px; font-family: monospace;'>[ ".strtoupper($type)." ] $msg</div>";
    }

    private static function renderHeader($task) {
        echo "<!DOCTYPE html><html><head><title>DevTool</title><style>
            body { background: #0c0c0c; color: #fff; font-family: monospace; padding: 20px; }
            .nav { display: flex; gap: 10px; margin-bottom: 20px; }
            .nav-btn { color: #fff; border: 1px solid #444; padding: 5px 10px; text-decoration: none; font-size: 12px; }
            .nav-btn:hover { background: #333; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { text-align: left; padding: 10px; border-bottom: 1px solid #222; }
        </style></head><body>";
        echo "<h2>Mini Lara DevTool - Task: $task</h2>";
        echo "<div class='nav'>
            <a href='?mode=db&task=status' class='nav-btn'>Status</a>
            <a href='?mode=db&task=migrate' class='nav-btn'>Migrate</a>
            <a href='?mode=db&task=seed' class='nav-btn'>Seed</a>
            <a href='?mode=db&task=reset' class='nav-btn' style='color:#ff3333'>Full Reset</a>
        </div>";
    }

    private static function renderFooter() {
        echo "</body></html>";
    }
}
