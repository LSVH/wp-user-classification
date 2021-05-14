<?php

namespace LSVH\WordPress\Plugin\UserClassification\Components;

use LSVH\WordPress\Plugin\UserClassification\Pages\UserPage;

class User extends BaseComponent
{
    public function load()
    {
        add_action('show_user_profile', [$this, 'render']);
        add_action('edit_user_profile', [$this, 'render']);
        add_action('personal_options_update', [$this, 'save']);
        add_action('edit_user_profile_update', [$this, 'save']);
    }

    public function render($user)
    {
        $can_read = $this->canEdit($user->ID);
        $can_edit = $this->canEdit($user->ID);

        if (!($can_read || $can_edit)) {
            return;
        }

        $domain = $this->plugin->getDomain();
        $meta = $this->getUserMeta($user->ID);
        $page = new UserPage($this->plugin);
        $title = __('User Classification', $domain);
        $subtitleLinkText = __('the settings page', $domain);
        $subtitleLink = '<a href="options-general.php?page=user-classification">' . $subtitleLinkText . '</a>';
        $subtitle = __('Manage what classifiers are active at', $domain) . ' ' . $subtitleLink . '.';

        print $page->render($this->getFields(), [
            'meta' => $meta,
            'disabled' => !$can_edit,
            'title' => $title,
            'subtitle' => $subtitle,
        ]);
    }

    public function save($user_id)
    {
        $domain = $this->plugin->getDomain();
        $values = array_key_exists($domain, $_POST) ? $_POST[$domain] : [];
        if ($this->canEdit($user_id) && is_array($values)) {
            $fields = $this->getFields();
            $namesOfFieldsWithOptions = array_filter(array_map(function ($field) {
                return array_key_exists('options', $field)
                && array_key_exists('name', $field)
                ? $field['name'] : null;
            }, $fields));

            foreach ($fields as $field) {
                $name = array_key_exists('name', $field) ? $field['name'] : null;
                $value = array_key_exists($name, $values) ? $values[$name] : null;
                $value = is_array($value) ? array_map('sanitize_text_field', $value) : sanitize_text_field($value);
                $key = !empty($name) ? $domain . '_' . $name : null;

                if (!empty($value) && in_array($name, $namesOfFieldsWithOptions)) {
                    $options = array_key_exists('options', $field) && is_array($field['options'])
                    ? $field['options'] : [];

                    $valuesOfOptions = array_filter(array_map(function ($option) {
                        return array_key_exists('value', $option) ? $option['value'] : null;
                    }, $options));

                    if (is_array($value) && empty(array_filter($value, function ($option) use ($valuesOfOptions) {
                        return in_array($option, $valuesOfOptions);
                    }))) {
                        break;
                    }

                    if (!is_array($value) && !in_array($value, $valuesOfOptions)) {
                        break;
                    }
                }

                update_user_meta($user_id, $key, $value);
            }
        }
    }

    private function getUserMeta($user_id)
    {
        $domain = $this->plugin->getDomain();
        $allMeta = get_user_meta($user_id);

        $meta = array_filter($allMeta, function ($key) use ($domain) {
            return preg_match("/^$domain/", $key);
        }, ARRAY_FILTER_USE_KEY);

        foreach ($meta as $key => $value) {
            unset($meta[$key]);
            $name = substr($key, strlen($domain) + 1);
            $value = is_array($value) ? $value[0] : $value;
            $meta[$name] = maybe_unserialize($value);
        }

        return $meta;
    }

    private function getFields()
    {
        $domain = $this->plugin->getDomain();
        $categories = $this->getCategories();

        $fields = [
            [
                'name' => 'category',
                'label' => __('Category', $domain),
                'options' => $categories,
            ],
        ];

        return array_map(function ($field) {
            $name = array_key_exists('name', $field) ? $field['name'] : null;

            if ($this->canMultiSelect($name)) {
                $field['multiple'] = true;
            }

            return $field;
        }, $fields);
    }

    private function getCategories()
    {
        return array_map(function ($term) {
            return [
                'label' => $term->name,
                'value' => $term->term_id,
            ];
        }, UserCategory::getItems());
    }

    private function canMultiSelect($name)
    {
        $options = $this->plugin->getOptions();
        $fieldsWithMultiSelect = array_key_exists('multiple', $options) ? $options['multiple'] : [];
        return in_array($name, $fieldsWithMultiSelect);
    }

    private function canEdit($user_id)
    {
        $domain = $this->plugin->getDomain();
        $can_edit_own = current_user_can('edit_own' . $domain) && get_current_user_id() == $user->ID;
        return current_user_can('edit_users') || current_user_can('edit' . $domain) || $can_edit_own;
    }

    private function canRead($user_id)
    {
        $domain = $this->plugin->getDomain();
        $can_read_own = current_user_can('read_own_' . $domain) && get_current_user_id() == $user->ID;
        return current_user_can('edit_users') || current_user_can('read' . $domain) || $can_read_own;
    }
}
