<?php

namespace LSVH\WordPress\Plugin\UserClassification\Components;

class UserCategory extends BaseComponent
{
    const TAXONOMY = 'user-category';

    public function load()
    {
        add_action('init', [$this, 'registerTaxonomy']);
        add_action('admin_menu', [$this, 'addTaxonomyToSubMenu']);
        add_action('admin_head-edit-tags.php', [$this, 'fixCurrentMenuSelector']);
        add_action('admin_head-term.php', [$this, 'fixCurrentMenuSelector']);
        add_filter('manage_users_columns', [$this, 'registerUserTableHeading']);
        add_filter('manage_users_custom_column', [$this, 'registerUserTableColumn'], 10, 3);
        add_action('pre_get_users', [$this, 'registerUserQueryFilter']);
    }

    public function registerTaxonomy()
    {
        register_taxonomy(self::TAXONOMY, null, [
            'public' => false,
            'show_ui' => true,
            'labels' => [
                'name' => $this->getPlural(),
                'singular_name' => $this->getSingular(),
            ],
        ]);
    }

    public function addTaxonomyToSubMenu()
    {
        $capability = 'edit_users';
        $slug = 'edit-tags.php?taxonomy=' . self::TAXONOMY;
        add_users_page('', $this->getPlural(), $capability, $slug, null);
    }

    public function fixCurrentMenuSelector()
    {
        if (self::TAXONOMY != htmlspecialchars($_GET['taxonomy'])) {
            return;
        }

        print <<<'EOD'
        <script type="text/javascript">
            jQuery(document).ready( function($) {
                $("#menu-posts, #menu-posts a")
                    .removeClass('wp-has-current-submenu')
                    .removeClass('wp-menu-open')
                    .addClass('wp-not-current-submenu');
                $("#menu-users, #menu-users > a")
                    .removeClass('wp-not-current-submenu')
                    .addClass('wp-has-current-submenu');
            });
        </script>
        EOD;
    }

    public function registerUserTableHeading($column)
    {
        $insert = [self::TAXONOMY => $this->getSingular()];
        $pos = array_search('posts', array_keys($column));
        if ($pos !== false) {
            $column = array_merge(
                array_slice($column, 0, $pos),
                $insert,
                array_slice($column, $pos)
            );
        } else {
            $column = array_merge($column, $insert);
        }
        return $column;
    }

    public function registerUserTableColumn($val, $column_name, $user_id)
    {
        if ($column_name === self::TAXONOMY) {
            $default = '-';
            $meta = get_user_meta($user_id, $this->plugin->getDomain() . '_category', true);

            if (is_array($meta)) {
                $categories = array_filter(array_map(function ($args) {
                    $term = get_term(intval($args));

                    return is_object($term) && property_exists($term, 'name')
                        ? $this->getFilterTermLink($term) : false;
                }, $meta));

                return empty($categories) ? $default  : implode(', ', $categories);
            }

            $term = get_term(intval($meta));

            return property_exists($term, 'name') ? $this->getFilterTermLink($term) : $default;
        }

        return $val;
    }

    public function registerUserQueryFilter($query)
    {
        global $pagenow;
        if (is_admin() && 'users.php' == $pagenow) {
            $key = $this->plugin->getDomain() . '_' . 'category';
            $values = is_array($_GET) && array_key_exists($key, $_GET)
            ? array_map('intval', explode(' ', sanitize_text_field($_GET[$key]))) : [];
            if (!empty($values)) {
                $queries = array_map(function ($value) use ($key) {
                    return [
                        'key' => $key,
                        'value' => $value,
                        'compare' => 'LIKE',
                    ];
                }, $values);
                $queries['relation'] = 'OR';
                $query->set('meta_key', $key);
                $query->set('meta_query', $queries);
            }
        }
    }

    public function getSingular()
    {
        $domain = $this->plugin->getDomain();

        return __('User Category', $domain);
    }

    public function getPlural()
    {
        $domain = $this->plugin->getDomain();

        return __('User Categories', $domain);
    }

    public static function getItems(): array
    {
        $terms = get_terms([
            'taxonomy' => self::TAXONOMY,
            'hide_empty' => false,
        ]);
        return is_array($terms) ? $terms : [];
    }

    private function getFilterTermLink($term)
    {
        $filter = $_GET;
        $key = $this->plugin->getDomain() . '_' . 'category';
        $value = is_array($_GET) && array_key_exists($key, $filter)
        ? array_map('intval', explode(' ', sanitize_text_field($filter[$key]))) : [];

        $id = $term->term_id;
        $active = in_array($id, $value);
        if ($active) {
            $value = array_diff($value, [$id]);
        } else {
            $value[] = $id;
        }

        $filter[$key] = implode('+', $value);
        $filter = array_filter($filter);
        $filter = implode('&', array_map(function ($k, $v) {
            return "$k=$v";
        }, array_keys($filter), $filter));

        $filter = empty($filter) ? '' : '?' . $filter;

        $label = $active ? "<strong>$term->name</strong>" : $term->name;

        return '<a href="users.php' . $filter . '">' . $label . '</a>';
    }
}
