<?php

namespace LSVH\WordPress\Plugin\UserClassification;

use LSVH\WordPress\Plugin\UserClassification\Components\User;
use LSVH\WordPress\Plugin\UserClassification\Components\UserCategory;
use LSVH\WordPress\Plugin\UserClassification\Components\Settings;

class Bootstrap
{
    private $plugin;

    public function __construct($file)
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $this->plugin = new Plugin(get_plugin_data($file, false));
    }

    public function exec()
    {
        $components = [
            new User($this->plugin),
            new Settings($this->plugin),
        ];

        $classifiers = $this->plugin->getOption('classifiers');
        if (is_array($classifiers) && in_array('category', $classifiers)) {
            $components[] = new UserCategory($this->plugin);
        }

        foreach ($components as $component) {
            $component->load();
        }
    }
}
