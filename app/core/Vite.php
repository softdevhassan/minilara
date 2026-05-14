<?php

namespace App;

class Vite
{
    /**
     * Get the Vite tags for the given entries.
     *
     * @param string|array $entries
     * @return string
     */
    public static function tags($entries): string
    {
        if (is_string($entries)) {
            $entries = [$entries];
        }

        $dev = $_ENV['APP_MODE'] === 'dev';
        
        // Check if Vite dev server is running
        $isHot = false;
        if ($dev) {
            $fp = @fsockopen('localhost', 5173, $errno, $errstr, 0.1);
            if ($fp) {
                $isHot = true;
                fclose($fp);
            }
        }

        if ($isHot) {
            $tags = '<script type="module" src="http://localhost:5173/@vite/client"></script>';
            foreach ($entries as $entry) {
                if (str_ends_with($entry, '.css')) {
                    $tags .= '<link rel="stylesheet" href="http://localhost:5173/' . $entry . '">';
                } else {
                    $tags .= '<script type="module" src="http://localhost:5173/' . $entry . '"></script>';
                }
            }
            return $tags;
        }

        // Production: read manifest
        $manifestPath = __DIR__ . '/../public/build/.vite/manifest.json';
        if (!file_exists($manifestPath)) {
            return '';
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $tags = '';

        foreach ($entries as $entry) {
            // Find key in manifest (Vite uses the relative path as key)
            $manifestKey = $entry;
            if (isset($manifest[$manifestKey])) {
                $file = asset('build/' . $manifest[$manifestKey]['file']);
                if (str_ends_with($file, '.css')) {
                    $tags .= '<link rel="stylesheet" href="' . $file . '">';
                } else {
                    $tags .= '<script type="module" src="' . $file . '"></script>';
                }

                // Add CSS imports if any
                if (isset($manifest[$manifestKey]['css'])) {
                    foreach ($manifest[$manifestKey]['css'] as $cssFile) {
                        $tags .= '<link rel="stylesheet" href="' . asset('build/' . $cssFile) . '">';
                    }
                }
            }
        }

        return $tags;
    }
}

