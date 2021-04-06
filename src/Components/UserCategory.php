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

    public function registerUserTableHeading($column) {
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

    public function registerUserTableColumn($val, $column_name, $user_id) {
        if ($column_name === self::TAXONOMY) {
            $meta = get_user_meta($user_id, $this->plugin->getDomain() . '_category', true);
            $term = get_term(intval($meta));

            return $term ? $term->name : '-';
        }

        return $val;
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
}
