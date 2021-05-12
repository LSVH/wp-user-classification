<?php

namespace LSVH\WordPress\Plugin\UserClassification\Pages;

class UserPage extends BasePage
{
    private $meta;
    private $disabled;

    public function render($fields, $opts = [])
    {
        $this->meta = $this->getOptByKey('meta', $opts, []);
        $this->disabled = $this->getOptByKey('disabled', $opts, false);

        $title = "<h2>" . $this->getOptByKey('title', $opts) . "</h2>";
        $subtitle = "<p>" . $this->getOptByKey('subtitle', $opts) . "</p>";
        $fields = $this->renderFields($fields);
        $table = $this->renderFormTable($fields);

        return $this->renderWrapper($title . $subtitle . $table);
    }

    public function parseField($field)
    {
        $name = array_key_exists('name', $field) ? $field['name'] : null;
        $field = parent::parseField($field);

        if (array_key_exists($name, $this->meta)) {
            $field['value'] = $this->meta[$name];
        }

        if ($this->disabled) {
            $field['disabled'] = true;
        }

        return $field;
    }
}
