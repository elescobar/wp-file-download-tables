<?php
/**
 * Plugin Name: WP File Download Tables
 * Description: A plugin to display downloadable file tables with metadata in posts and pages.
 * Version: 1.0
 * Author: DaVik
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Define constants
define('WP_FDT_VERSION', '1.0');
define('WP_FDT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_FDT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once WP_FDT_PLUGIN_DIR . 'includes/class-admin.php';
require_once WP_FDT_PLUGIN_DIR . 'includes/class-table-render.php';

// Initialize plugin
function wp_fdt_init() {
    new WP_FDT_Admin();
}
add_action('plugins_loaded', 'wp_fdt_init');

function wp_fdt_enqueue_assets() {
    if (!is_admin() && isset($GLOBALS['post']) && has_shortcode($GLOBALS['post']->post_content, 'file_table')) {
        wp_enqueue_style('wp-fdt-style', WP_FDT_PLUGIN_URL . 'assets/style.css', [], WP_FDT_VERSION);
    }
}
add_action('wp_enqueue_scripts', 'wp_fdt_enqueue_assets');


function wp_fdt_register_gutenberg_block() {
    wp_register_script(
        'wp-fdt-block',
        WP_FDT_PLUGIN_URL . 'blocks/file-table-block.js',
        [ 'wp-blocks', 'wp-element', 'wp-data' ],
        WP_FDT_VERSION,
        true
    );

    register_block_type( 'wp-fdt/file-table', [
        'editor_script' => 'wp-fdt-block',
        'render_callback' => function( $attributes ) {
            $table_id = isset( $attributes['tableId'] ) ? esc_attr( $attributes['tableId'] ) : '';
            return do_shortcode( '[file_table id="' . $table_id . '"]' );
        }
    ]);
}
add_action( 'init', 'wp_fdt_register_gutenberg_block' );

function wp_fdt_register_rest_routes() {
    register_rest_route('wp-fdt/v1', '/tables/', array(
        'methods'  => 'GET',
        'callback' => 'wp_fdt_get_tables',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'wp_fdt_register_rest_routes');

function wp_fdt_get_tables() {
    global $wpdb;
    $raw_data = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
            'wp_fdt_file_tables'
        )
    );

    if (empty($raw_data)) {
        return rest_ensure_response([]);
    }

    $tables = json_decode($raw_data, true);
    if (!is_array($tables)) {
        return rest_ensure_response([]);
    }

    $table_list = [];
    foreach ($tables as $table_id => $files) {
        $table_list[] = ['id' => $table_id, 'name' => 'Table ' . $table_id];
    }

    return rest_ensure_response($table_list);
}
require_once plugin_dir_path(__FILE__) . 'includes/shortcode.php';

function wp_fdt_enqueue_admin_assets($hook) {
    if ($hook === 'toplevel_page_wp-fdt') {
        wp_enqueue_script('wp-fdt-admin-script', WP_FDT_PLUGIN_URL . 'assets/admin-script.js', ['jquery'], WP_FDT_VERSION, true);
        wp_enqueue_style('wp-fdt-admin-style', WP_FDT_PLUGIN_URL . 'assets/admin-style.css', [], WP_FDT_VERSION);
    }
}
add_action('admin_enqueue_scripts', 'wp_fdt_enqueue_admin_assets');

