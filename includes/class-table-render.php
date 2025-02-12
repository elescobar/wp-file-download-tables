<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_FDT_Table_Render {
    public static function render_table($table_id = '') {
        $tables = get_option('wp_fdt_file_tables', []);
        if (empty($tables)) {
            return '<p>No files available.</p>';
        }
    
        $tables = json_decode($tables, true);
        if (!$tables || !is_array($tables)) {
            return '<p>Invalid file data.</p>';
        }
    
        $table_data = $tables[$table_id] ?? [];
        if (empty($table_data)) {
            return '<p>No files in this table.</p>';
        }
    
        ob_start();
        ?>
        <div class="wp-fdt-container">
            <input type="text" class="wp-fdt-filter" placeholder="Search files...">
            <table class="wp-fdt-table">
                <thead>
                    <tr>
                        <th data-order="">File Name</th>
                        <th data-order="">Description</th>
                        <th data-order="">Last Update</th>
                        <th data-order="">File Size</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($table_data as $file) : ?>
                        <tr>
                            <td><?php echo esc_html($file['name'] ?? 'Unknown'); ?></td>
                            <td><?php echo esc_html($file['description'] ?? 'No description'); ?></td>
                            <td><?php echo esc_html($file['last_update'] ?? 'N/A'); ?></td>
                            <td><?php echo esc_html($file['size'] ?? 'Unknown'); ?></td>
                            <td><a href="<?php echo esc_url($file['url'] ?? '#'); ?>" target="_blank">Download</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }    
}

// Register a shortcode to display the table
function wp_fdt_shortcode($atts) {
    $atts = shortcode_atts(['id' => ''], $atts);
    return WP_FDT_Table_Render::render_table($atts['id']);
}
add_shortcode('file_table', 'wp_fdt_shortcode');
