<?php

namespace LSVH\WordPress\Plugin\UserClassification\Pages;

use LSVH\WordPress\Plugin\UserClassification\Base;
use LSVH\WordPress\Plugin\UserClassification\Utilities\Fields;

abstract class BasePage extends Base implements Page
{
    public function renderFields($fields)
    {
        return implode('', array_map(function ($field) {
            return (new Fields($this->parseField($field)))->renderFieldRow();
        }, $fields));
    }

    public function parseField($field)
    {
        $domain = $this->plugin->getDomain();

        if (array_key_exists('name', $field)) {
            $name = $field['name'];
            $field['name'] = $domain . '[' . $name . ']';

            $id = array_key_exists('id', $field) ? $field['id'] : $name;
            $field['id'] = $domain . '_' . $id;
        }

        return $field;
    }

    protected function renderWrapper($content)
    {
        $domain = $this->plugin->getDomain();
        return '<div class="' . $domain . '">' . $content . '</div>';
    }

    protected function renderForm($content, $opts = [])
    {
        $domain = $this->plugin->getDomain();
        $action = array_key_exists('action', $opts) ? $opts['action'] : 'options.php';
        $nonce = array_key_exists('nonce', $opts) ? $opts['nonce'] : $this->renderFormNonce();
        $submit = array_key_exists('submit', $opts) ? $opts['submit'] : $this->renderFormSubmit();
        return '<form method="post" action="' . $action . '">' . $nonce . $content . $submit . '</form>';
    }

    protected function renderFormNonce() {
        $domain = $this->plugin->getDomain();
        ob_start();
        settings_fields($domain);
        return ob_get_clean();
    }

    protected function renderFormSubmit() {
        return get_submit_button();
    }

    protected function renderFormTable($content)
    {
        return '<table class="form-table" role="presentation">' . $content . '</table>';
    }

    protected function getOptByKey($key, $opts, $fallback = null) {
        return is_array($opts) && array_key_exists($key, $opts) ? $opts[$key] : $fallback;
    }
}
