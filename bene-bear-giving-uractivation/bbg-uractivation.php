<?php

/**
 * Plugin Name: BBG User Activation
 * Plugin URI:  
 * Description: This is the Benebear Extension Plugin.
 * Version:     1.0
 * Author:      BBIL
 * Author URI:  https://blubirdinteractive.com
 * Text Domain: bbgurcode
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package     BbgUrActivation
 * @author      BBIL
 * @copyright   2022 BBIL
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 *
 * Prefix:      BBG
 */

defined('ABSPATH') || die('No script kiddies please!');

define('BBG_VERSION', 'BbgUrActivation');
define('BBG_PLUGIN', __FILE__);
define('BBG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BBG_PLUGIN_PATH', plugin_dir_path(__FILE__));

add_action('plugins_loaded', 'BBG_plugin_init');
/**
 * Load localization files
 *
 * @return void
 */
function BBG_plugin_init()
{
    load_plugin_textdomain('bbgurcode', false, dirname(plugin_basename(__FILE__)) . '/languages');
}




require BBG_PLUGIN_PATH . 'includes/form-handler.php';
require BBG_PLUGIN_PATH . 'includes/database.php';
require BBG_PLUGIN_PATH . 'includes/menu.php';
require BBG_PLUGIN_PATH . 'includes/enqueue.php';
require BBG_PLUGIN_PATH . 'includes/my-account.php';
require BBG_PLUGIN_PATH . 'includes/urcode-cpt.php';
require BBG_PLUGIN_PATH . 'includes/urcode-taxonomy.php';
require BBG_PLUGIN_PATH . 'includes/urcode-metabox.php';
require BBG_PLUGIN_PATH . 'includes/urcode-custom-column.php';


/**
 * Add a menu to admin lebel
 * @return void
 * @param #
 * 
 * 
 */

register_activation_hook(__FILE__, 'bbg_do_things_upon_plugin_activation');

if (!function_exists('bbg_do_things_upon_plugin_activation')) {

    function bbg_do_things_upon_plugin_activation()
    {
        flush_rewrite_rules();
    }
}

add_action('admin_menu', 'bbg_user_reg_menus');

function bbg_user_reg_menus()
{
    $hooks = add_menu_page(__('User Activation', 'bbg'), __('User Activation', 'bbg'), 'manage_options', 'bbg-user-activation', 'bbg_user_activation_page', 'dashicons-book', 77);
    add_action('admin_head-' . $hooks . '', 'bbg_user_reg_enqueue_script');
}
add_action('admin_init', 'bbg_custom_form_handler');




// Kick out the db staff || Update, Insert etc 
// is post type urcode 
$post_type = $_GET['post_type'] ?? '';

if (isset($post_type) && 'urcode' == $post_type && is_admin()) {
    add_action('wp_loaded', 'get_all_code_from_db');
}
function get_all_code_from_db()
{

    global $wpdb;
    $table = $wpdb->prefix . 'bbg_user_registrations';

    $sql = "SELECT * FROM $table";
    $result = $wpdb->get_results($sql);

    foreach ($result as $code) {

        $is_exists = "SELECT * FROM $wpdb->posts WHERE post_title = '$code->code' LIMIT 50";
        $is_ex = $wpdb->get_results($is_exists);

        if (count($is_ex) == 0) {
            $arr = array(
                'post_type' => 'urcode',
                'post_title' => wp_strip_all_tags( $code->code ),
                'post_date' => $code->created_at,
                'post_status' => 'publish',
                'meta_input' => [
                    'bbg_urcode_email' => wp_strip_all_tags($code->email),
                    'bbg_urcode_uid' => wp_strip_all_tags($code->user_id),
                    'bbg_urcode_is_used' => wp_strip_all_tags($code->is_used),
                ],
            );

            $insert_post = wp_insert_post($arr);
        }
    }

    foreach ($result as $code) {
        $arrup = array(
            'ID' => $code->post_id,
            'post_type' => 'urcode',
            'post_status' => 'publish',
            'post_date' => $code->created_at,
            'meta_input' => [
                'bbg_urcode_email' => $code->email,
                'bbg_urcode_uid' => $code->user_id,
                'bbg_urcode_is_used' => $code->is_used,
            ],
        );

        if (!empty($code->email) || $code->email != null || $code->created_at != null) {
            $update_posts = wp_update_post($arrup);
        }
    }


    if (is_wp_error($update_posts)) {
        wp_die($update_posts->get_error_message());
    }


    // Get all the UR Code type posts
    $psql = "SELECT * FROM $wpdb->posts WHERE post_type = 'urcode' AND post_status='publish'";
    $res = $wpdb->get_results($psql);

    // Get all the ids for update 

    $all_post_ids = [];
    foreach ($result as $post) {
        $all_post_ids[] = $post->post_id;
    }

    foreach ($all_post_ids as $id) {
        if (empty($id) || null == $id) {
            foreach ($res  as $p) {
                // Update our custom table with the inserted id
                $update_post = $wpdb->update(
                    $table,
                    array(
                        'post_id' => $p->ID,
                    ),
                    array(
                        'code' => $p->post_title
                    )
                );
            }
        }
    }

    // Insert primary code to our new table 
    // require_once BBG_PLUGIN_PATH . 'assets/code/unique-code.php';
    // $arr = explode(",", $unique_codes);

    // $db_table = $wpdb->prefix . 'bbg_user_registrations';


    // for ($i = 0; $i < 1003; $i++) {
    //     $data = strip_tags($arr[$i]);
    //     $data = trim($data);
    //     $data = stripcslashes($data);
    //     $data = stripslashes($data);

    //     $insert_all_codes = $wpdb->insert(
    //         $db_table,
    //         [
    //             'code' => $data
    //         ],
    //         [
    //             '%s'
    //         ]
    //     );
    // }


}
