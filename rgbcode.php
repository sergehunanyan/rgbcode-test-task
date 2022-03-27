<?php
/**
 * Plugin Name: RGBCode
 * Plugin URI: https://wordpress.org/plugins/rgb-code/
 * Description: RGBCode test task
 * Author: RGBCode
 * Author URI: https://rgbcode.com/
 * Version: 1.0.0
 * Text Domain: rgb-code
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Required constants
 */
define('USERS_TABLE_PLUGIN_VERSION', '1.0.0');
define('USERS_TABLE_PLUGIN_MAIN_FILE', __FILE__);
define('USERS_TABLE_PLUGIN_URL', untrailingslashit(plugins_url('', USERS_TABLE_PLUGIN_MAIN_FILE)));

class UsersTablePlugin
{

    private $per_page = 10;

    private $orderby = "user_login";

    private $order = "ASC";

    /**
     * UsersTable constructor.
     */
    function __construct()
    {
        add_shortcode('rgb_users_table', [$this, 'usersTableShortcode']);
        add_action('wp_enqueue_scripts', [$this, 'usersTablePluginAssets']);

        add_action('wp_ajax_nopriv_users_table_generate', [$this, 'usersTableGenerate']);
        add_action('wp_ajax_users_table_generate', [$this, 'usersTableGenerate']);
    }

    /**
     * Styles and scripts
     */
    public function usersTablePluginAssets()
    {
        wp_enqueue_style('users_table_plugin', USERS_TABLE_PLUGIN_URL . '/assets/css/users-table.css', array(), USERS_TABLE_PLUGIN_VERSION);
        wp_enqueue_style('users_table_plugin_fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/fontawesome.min.css');

        wp_enqueue_script('jquery');
        wp_enqueue_script('users_table_plugin', USERS_TABLE_PLUGIN_URL . '/assets/js/users-table.js', array(), USERS_TABLE_PLUGIN_VERSION, true);
        wp_localize_script('users_table_plugin', 'rgb_users_table', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ajax-nonce')
        ));
        wp_enqueue_script('users_table_plugin_fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/js/all.min.js');
    }

    public function usersTableShortcode($attr)
    {
        global $wp_roles;

        if (current_user_can('administrator')) {
            $args = shortcode_atts(array(

                'per_page' => $this->per_page,
                'orderby' => $this->orderby,
                'order' => $this->order

            ), $attr);

            $args['per_page'] = intval($args['per_page']) ? intval($args['per_page']) : $this->per_page;
            $order_arr = ['ASC', 'DESC'];
            $args['order'] = in_array($args['order'], $order_arr) ? $args['order'] : 'ASC';
            $orderby_arr = ['user_login', 'display_name'];
            $args['orderby'] = in_array($args['orderby'], $orderby_arr) ? $args['orderby'] : 'user_login';

            $user_args = [
                'number' => $args['per_page'],
                'orderby' => $args['orderby'],
                'order' => $args['order'],
            ];

            $users = get_users($user_args);

            $roles = $wp_roles->roles;

            ob_start();
            include_once 'includes/templates/table.php';
            return ob_get_clean();
        }
    }

    public function usersTableGenerate()
    {
        if (wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
            $page = intval($_POST['page']) ?? 1;
            $per_page = intval($_POST['per_page']) ?? $this->per_page;
            $orderby = sanitize_text_field($_POST['orderby']) ?? $this->orderby;
            $order = sanitize_text_field($_POST['order']) ?? $this->order;
            $role = sanitize_text_field($_POST['role']) ?? '';
            $cur_page = $page;
            $page -= 1;
            $previous_btn = true;
            $next_btn = true;
            $first_btn = true;
            $last_btn = true;
            $start = $page * $per_page;

            $args = array(
                'number' => $per_page,
                'orderby' => $orderby,
                'order' => $order,
                'offset' => $start
            );

            if ($role != '') {
                $args['role'] = $role;
            }

            $users_content = $this->usersTableContent($args);

            $total_args = [];
            if ($role != '') {
                $total_args['role'] = $role;
            }
            $count = count(get_users($total_args));

            $no_of_paginations = ceil($count / $per_page);

            if ($cur_page >= 7) {
                $start_loop = $cur_page - 3;
                if ($no_of_paginations > $cur_page + 3)
                    $end_loop = $cur_page + 3;
                else if ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {
                    $start_loop = $no_of_paginations - 6;
                    $end_loop = $no_of_paginations;
                } else {
                    $end_loop = $no_of_paginations;
                }
            } else {
                $start_loop = 1;
                if ($no_of_paginations > 7)
                    $end_loop = 7;
                else
                    $end_loop = $no_of_paginations;
            }

            $pag_container = "<ul>";

            if ($first_btn && $cur_page > 1) {
                $pag_container .= "<li p='1' class='active'>First</li>";
            } else if ($first_btn) {
                $pag_container .= "<li p='1' class='inactive'>First</li>";
            }

            if ($previous_btn && $cur_page > 1) {
                $pre = $cur_page - 1;
                $pag_container .= "<li p='$pre' class='active'>Previous</li>";
            } else if ($previous_btn) {
                $pag_container .= "<li class='inactive'>Previous</li>";
            }
            for ($i = $start_loop; $i <= $end_loop; $i++) {

                if ($cur_page == $i)
                    $pag_container .= "<li p='$i' class = 'selected' >{$i}</li>";
                else
                    $pag_container .= "<li p='$i' class='active'>{$i}</li>";
            }

            if ($next_btn && $cur_page < $no_of_paginations) {
                $nex = $cur_page + 1;
                $pag_container .= "<li p='$nex' class='active'>Next</li>";
            } else if ($next_btn) {
                $pag_container .= "<li class='inactive'>Next</li>";
            }

            if ($last_btn && $cur_page < $no_of_paginations) {
                $pag_container .= "<li p='$no_of_paginations' class='active'>Last</li>";
            } else if ($last_btn) {
                $pag_container .= "<li p='$no_of_paginations' class='inactive'>Last</li>";
            }

            $pag_container = $pag_container . "</ul>";

            $data = [
                'users' => $users_content,
                'pagination' => $pag_container,
            ];
            echo json_encode($data);
        }
        die;
    }

    public function usersTableContent($args)
    {
        $users = get_users($args);

        $output = '';
        foreach ($users as $user) {
            $output .= '<tr>
                            <td>' . esc_html($user->user_login) . '</td>
                            <td>' . esc_html($user->display_name) . '</td>
                            <td>' . esc_html($user->roles[0]) . '</td>
                        </tr>';
        }

        if ($output == '') {
            $output = '<tr>
                            <td>No users found</td>
                        </tr>';
        }

        return $output;
    }

}

new UsersTablePlugin();