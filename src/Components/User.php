<?php

namespace LSVH\WordPress\Plugin\UserClassification\Components;

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
        $meta = $this->getUserMeta($user->ID);
        $domain = $this->plugin->getDomain();
        $can_read = $this->canEdit($user->ID);
        $can_edit = $this->canEdit($user->ID);

        if (!($can_read || $can_edit)) {
            return;
        }

        print $this->renderTemplate('user.html', [
            'meta' => $meta,
            'domain' => $domain,
            'disabled' => !$can_edit,
            'options' => $this->plugin->getOptions(),
            'title' => __('User Classification', $domain),
            'fields' => $this->getFields(),
        ]);
    }

    public function save($user_id)
    {
        $domain = $this->plugin->getDomain();
        $values = $_POST[$domain];
        if ($this->canEdit($user_id) && is_array($fields) && !empty($fields)) {
            $fields = $this->getFields();
            $namesOfFields = array_filter(array_map(function ($field) {
                return array_key_exists('name', $field) ? $field['name'] : null;
            }, $fields));
            $namesOfFieldsWithOptions = array_filter(array_map(function ($field) {
                return array_key_exists('options', $field)
                    && array_key_exists('name', $field)
                    ? $field['name'] : null;
            }, $fields));

            foreach ($values as $key => $value) {
                $key = sanitize_text_field($domain . '_' . $key);
                $value = sanitize_text_field($value);

                if (!in_array($key, $namesOfFields)) {
                    break;
                }

                if (in_array($key, $namesOfFieldsWithOptions)) {
                    $options = is_array($fields[$key]['options']) ? $fields[$key]['options'] : [];
                    $valuesOfOptions = array_filter(array_map(function ($option) {
                        return array_key_exists('value', $option) ? $option['value'] : null;
                    }, $options));

                    if (!in_array($value, $valuesOfOptions)) {
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
            return str_starts_with($key, $domain);
        }, ARRAY_FILTER_USE_KEY);

        foreach ($meta as $key => $value) {
            unset($meta[$key]);
            $meta[substr($key, strlen($domain) + 1)] = is_array($value) ? $value[0] : $value;
        }

        return $meta;
    }

    private function getFields()
    {
        $domain = $this->plugin->getDomain();
        $categories = $this->getCategories();

        return [
            [
                'name' => 'category',
                'label' => __('Category', $domain),
                'options' => $categories,
            ],
        ];
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
