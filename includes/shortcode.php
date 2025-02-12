<?php
function wp_fdt_render_table($atts) {
    global $wpdb;

    // Get saved tables from database
    $raw_data = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
            'wp_fdt_file_tables'
        )
    );

    if (empty($raw_data)) {
        return '<p>No tables available.</p>';
    }

    $tables = json_decode($raw_data, true);
    $table_id = isset($atts['id']) ? esc_attr($atts['id']) : '';

    if (!isset($tables[$table_id])) {
        return '<p>Table not found.</p>';
    }

    // Build table HTML
    $output = '<div class="wp-fdt-table-container" role="region" aria-labelledby="table-title">';
    $output .= '<table class="wp-fdt-table" role="table">';
    $output .= '<caption id="table-title">Downloadable Files</caption>';
    $output .= '<thead>
    <tr>
        <th scope="col">File Name</th>
        <th scope="col">Description</th>
        <th scope="col">Download</th>
        <th scope="col">Last Updated</th>
        <th scope="col">Size</th>
    </tr>
</thead>';
    $output .= '<tbody>';

    foreach ($tables[$table_id] as $file) {
        $output .= '<tr>';
        $output .= '<td>' . esc_html($file['name']) . '</td>';
        $output .= '<td>' . esc_html($file['description']) . '</td>';
        $output .= '<td><a href="' . esc_url($file['url']) . '" target="_blank" rel="noopener noreferrer" aria-label="Download ' . esc_attr($file['name']) . '">Download</a></td>';
        $output .= '<td>' . esc_html($file['last_update']) . '</td>';
        $output .= '<td>' . esc_html($file['size']) . '</td>';
        $output .= '</tr>';
    }

    $output .= '</tbody></table>';
    $output .= '</div>';

    return $output;
}
add_shortcode('file_table', 'wp_fdt_render_table');
