<?php

namespace LSVH\WordPress\Plugin\UserClassification\Pages;

class SettingsPage extends BasePage
{
    public function render($fields, $opts = [])
    {
        $title = "<h2>" . $this->getOptByKey('title', $opts) . "</h2>";
        $fields = $this->renderFields($fields);
        $table = $this->renderFormTable($fields);
        $form = $this->renderForm($table);

        return $this->renderWrapper($title . $form);
    }

    public function parseField($field)
    {
        $name = array_key_exists('name', $field) ? $field['name'] : null;

        $field = parent::parseField($field);

        $field['value'] = $this->plugin->getOption($name);

        return $field;
    }
}
