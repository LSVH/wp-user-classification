<?php

namespace LSVH\WordPress\Plugin\UserClassification\Components;

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
        $domain = $this->plugin->getDomain();
        $classifiers = $this->getClassifiers();

        print $this->renderTemplate('settings.html', [
            'domain' => $domain,
            'options' => $this->plugin->getOptions(),
            'title' => $this->plugin->getName(),
            'nonce' => $this->getFormNonce(),
            'submit' => get_submit_button(),
            'fields' => [
                [
                    'name' => 'classifiers',
                    'label' => __('Toggle classifiers', $domain),
                    'multiple' => true,
                    'options' => $classifiers,
                ],
            ],
        ]);
    }

    public function getFormNonce()
    {
        $domain = $this->plugin->getDomain();
        ob_start();
        settings_fields($domain);
        return ob_get_clean();
    }

    public function getClassifiers() {
        $domain = $this->plugin->getDomain();

        return [
            [
                'label' => __('Categories', $domain),
                'value' => 'category',
            ],
        ];
    }
}
