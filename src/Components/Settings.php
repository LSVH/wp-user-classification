<?php

namespace LSVH\WordPress\Plugin\UserClassification\Components;

use LSVH\WordPress\Plugin\UserClassification\Pages\SettingsPage;

class Settings extends BaseComponent
{
    public function load()
    {
        add_action('admin_init', [$this, 'registerSetting']);
        add_action('admin_menu', [$this, 'registerMenuPage']);
    }

    public function registerSetting()
    {
        $domain = $this->plugin->getDomain();
        register_setting($domain, $domain);
    }

    public function registerMenuPage()
    {
        $name = $this->plugin->getName();
        $domain = $this->plugin->getDomain();
        $capability = 'manage_options';

        add_options_page($name, $name, $capability, $domain, [$this, 'renderSettings']);
    }

    public function renderSettings()
    {
        $page = new SettingsPage($this->plugin);

        print $page->render($this->getFields(), [
            'title' => $this->plugin->getName(),
        ]);
    }

    private function getFields()
    {
        $domain = $this->plugin->getDomain();
        $classifiers = $this->getClassifiers();

        return [
            [
                'name' => 'classifiers',
                'label' => __('Enable classification by', $domain),
                'multiple' => true,
                'options' => $classifiers,
            ],
            [
                'name' => 'multiple',
                'label' => __('Enable multi-select for', $domain),
                'multiple' => true,
                'options' => $classifiers,
            ],
        ];
    }

    private function getClassifiers()
    {
        $domain = $this->plugin->getDomain();

        return [
            [
                'label' => __('Categories', $domain),
                'value' => 'category',
            ],
        ];
    }
}
