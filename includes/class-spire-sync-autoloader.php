<?php

namespace SpireSync;

/**
 * Class Spire_Sync_Autoloader
 *
 * Handles autoloading of plugin classes.
 */
class Spire_Sync_Autoloader {

    /**
     * The base directory for the plugin.
     *
     * @var string
     */
    private $base_dir;

    /**
     * Constructor.
     *
     * @param string $base_dir The base directory for the plugin.
     */
    public function __construct($base_dir) {
        $this->base_dir = $base_dir;
    }

    /**
     * Register the autoloader.
     */
    public function register() {
        spl_autoload_register([$this, 'autoload']);
    }

    /**
     * Autoload classes.
     *
     * @param string $class The fully-qualified class name.
     */
    public function autoload($class) {
        // Only autoload our plugin's classes
        if (strpos($class, 'SpireSync\\') !== 0) {
            return;
        }

        // Convert namespace to file path
        $file = str_replace('SpireSync\\', '', $class);
        $file = str_replace('\\', '/', $file);
        $file = strtolower($file);
        $file = str_replace('_', '-', $file);

        // Add 'class-' prefix to match our file naming convention
        $file = 'class-' . $file;

        // Build the full path
        $path = $this->base_dir . '/includes/' . $file . '.php';

        // Load the file if it exists
        if (file_exists($path)) {
            require_once $path;
        }
    }
} 