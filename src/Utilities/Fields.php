<?php

namespace LSVH\WordPress\Plugin\UserClassification\Utilities;

class Fields
{
    private $id;
    private $name;
    private $label;
    private $type;
    private $placeholder;
    private $multiple;
    private $disabled;
    private $options;
    private $value;

    public function __construct($args = [])
    {
        $this->id = self::getEscapedArgByKey('id', $args);
        $this->name = self::getEscapedArgByKey('name', $args);
        $this->label = self::getEscapedArgByKey('label', $args);
        $this->type = self::getEscapedArgByKey('type', $args);
        $this->placeholder = self::getEscapedArgByKey('placeholder', $args);
        $this->multiple = self::getEscapedArgByKey('multiple', $args, false);
        $this->disabled = self::getEscapedArgByKey('disabled', $args, false);
        $this->options = self::getArgByKey('options', $args);
        $this->value = self::getArgByKey('value', $args);
    }

    public function renderFieldRow()
    {
        return '<tr><th>' . $this->renderLabel() . '</th><td>' . $this->renderField() . '</td></tr>';
    }

    public function renderLabel()
    {
        return '<label for="' . $this->id . '">' . $this->label . '</label>';
    }

    public function renderField()
    {
        return !empty($this->options) && is_array($this->options) ? (
            boolval($this->multiple)
            ? $this->renderCheckboxes()
            : $this->renderSelect()
        ) : $this->renderInput();
    }

    public function renderInput()
    {
        $attrs = $this->renderAttributes(['id', 'name', 'disabled', 'type', 'placeholder', 'value']);

        return "<input$attrs />";
    }

    public function renderSelect()
    {
        if (empty($this->options) || !is_array($this->options)) {
            return $this->renderNoOptionsFound();
        }

        $attrs = $this->renderAttributes(['id', 'name', 'disabled']);
        $placeholder = '<option disabled' . (empty($this->value) ? ' selected' : null) . '>---</option>';
        $options = implode('', array_map(function ($option) {
            $value = $this->getEscapedArgByKey('value', $option);
            $label = $this->getEscapedArgByKey('label', $option);
            $selected = $this->value === $value;
            $attrs = $this->renderAttributes([], ['value' => $value, 'selected' => $selected]);
            return "<option$attrs>$label</option>";
        }, $this->options));

        return "<select$attrs>$options</select>";
    }

    public function renderCheckboxes()
    {
        if (empty($this->options) || !is_array($this->options)) {
            return $this->renderNoOptionsFound();
        }

        $this->type = 'checkbox';
        $this->name = $this->name . '[]';

        return implode('', array_map(function ($key, $option) {
            $id = $this->id . '_' . $key;
            $value = $this->getEscapedArgByKey('value', $option);
            $label = $this->getEscapedArgByKey('label', $option);
            $checked = in_array($value, is_array($this->value) ? $this->value : []);
            $attrs = $this->renderAttributes(['type', 'name', 'disabled'], [
                'id' => $id,
                'value' => $value,
                'checked' => $checked
            ]);
            $field = "<input$attrs/> <span>$label</span>";
            return '<fieldset><label for="' . $id . '">' . $field . '</label></fieldset>';
        }, array_keys($this->options), $this->options));
    }

    public function renderAttributes($attrNames, $merge = [])
    {
        $attrs = [];
        if (!empty($attrNames)) {
            $attrs = array_filter(get_object_vars($this), function ($attr) use ($attrNames) {
                return in_array($attr, $attrNames) && boolval($this->{$attr});
            }, ARRAY_FILTER_USE_KEY);
        }

        if (!empty($merge)) {
            $attrs = array_merge($attrs, array_filter($merge));
        }

        $htmlAttrs = implode(' ', array_map(function ($key, $value) {
            if (is_bool($value)) {
                return $key;
            }
            return $key . '="' . $value . '"';
        }, array_keys($attrs), $attrs));

        return empty($htmlAttrs) ? '' : ' ' . $htmlAttrs;
    }

    public function renderNoOptionsFound()
    {
        $message = sprintf(__('No options found for "%s".'), strtolower($this->label));
        return "<em>" . $message . "</em>";
    }

    private static function getArgByKey($key, $args, $fallback = null)
    {
        return is_array($args) && array_key_exists($key, $args) ? $args[$key] : $fallback;
    }

    private static function getEscapedArgByKey($key, $args, $fallback = null)
    {
        return esc_html(self::getArgByKey($key, $args));
    }
}
