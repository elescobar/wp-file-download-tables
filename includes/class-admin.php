<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_FDT_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'File Download Tables',
            'File Tables',
            'manage_options',
            'wp-fdt',
            [$this, 'settings_page'],
            'dashicons-download'
        );
    }

    public function register_settings() {
        register_setting('wp_fdt_settings_group', 'wp_fdt_file_tables', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_tables'],
        ]);
    }
    
    public function sanitize_tables($input) {
        if (!is_array($input)) {
            return [];
        }
    
        foreach ($input as $table_id => $files) {
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $table_id)) {
                continue;
            }
    
            foreach ($files as $key => $file) {
                $files[$key]['name'] = sanitize_text_field($file['name']);
                $files[$key]['description'] = sanitize_text_field($file['description']);
                $files[$key]['url'] = esc_url_raw($file['url']);
                $files[$key]['last_update'] = sanitize_text_field($file['last_update']);
                $files[$key]['size'] = sanitize_text_field($file['size']);
            }
            $input[$table_id] = $files;
        }
    
        return $input;
    }  

    public function settings_page() {
        global $wpdb;
$raw_data = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
        'wp_fdt_file_tables'
    )
);

if (empty($raw_data)) {
    error_log("Warning: Retrieved data is empty.");
    $raw_data = '{}';
}

error_log("Raw Retrieved Data (Direct DB): " . print_r($raw_data, true));

$tables = json_decode($raw_data, true);


        if (!is_array($tables)) {
            error_log("Error: JSON decoding failed. Forcing empty array.");
            $tables = [];
        }

        error_log("Decoded Retrieved Data: " . print_r($tables, true));


    
        if (empty($tables)) {
            echo "<p>No tables found in the database.</p>";
        } else {
            echo "<p>Loaded tables from the database.</p>";
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wp_fdt_files'])) {
            check_admin_referer('wp_fdt_save_files');
        
            $new_tables = json_decode(stripslashes($_POST['wp_fdt_files']), true);
        
            error_log("Raw Data Received: " . print_r($_POST['wp_fdt_files'], true));
            error_log("Decoded Data to Save: " . print_r($new_tables, true));
        
            if (!is_array($new_tables)) {
                error_log("Error: Data is not an array.");
                wp_die(__('Invalid data format.'));
            }
        
            // Convert array to JSON string
            $encoded_data = wp_json_encode($new_tables);
        
            error_log("Final Encoded Data to Save: " . print_r($encoded_data, true));
        
            // Ensure previous data is removed before saving fresh JSON data
            delete_option('wp_fdt_file_tables'); 
            add_option('wp_fdt_file_tables', $encoded_data, '', 'no');
        
            // Verify what was actually saved
            $saved_value = get_option('wp_fdt_file_tables');
            error_log("After Save: " . print_r($saved_value, true));
        }
        
?>
    
        <div class="wrap">
            <h1>Manage File Download Tables</h1>
            <form method="post">
                <?php wp_nonce_field('wp_fdt_save_files'); ?>
    
                <div id="file-tables-container">
                    <?php foreach ($tables as $table_id => $files) : ?>
                        <div class="file-table" data-table-id="<?php echo esc_attr($table_id); ?>">
                            <h2>Table ID: <?php echo esc_html($table_id); ?></h2>
                            <input type="text" class="table-id" value="<?php echo esc_attr($table_id); ?>" placeholder="Table ID">
                            <button type="button" class="remove-table button">Remove Table</button>
    
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th>File Name</th>
                                        <th>Description</th>
                                        <th>URL</th>
                                        <th>Last Update</th>
                                        <th>Size</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($files as $file) : ?>
                                        <tr>
                                            <td><input type="text" value="<?php echo esc_attr($file['name'] ?? ''); ?>" class="file-name"></td>
                                            <td><input type="text" value="<?php echo esc_attr($file['description'] ?? ''); ?>" class="file-description"></td>
                                            <td><input type="text" value="<?php echo esc_url($file['url'] ?? ''); ?>" class="file-url"></td>
                                            <td><input type="text" value="<?php echo esc_attr($file['last_update'] ?? ''); ?>" class="file-last-update"></td>
                                            <td><input type="text" value="<?php echo esc_attr($file['size'] ?? ''); ?>" class="file-size"></td>
                                            <td><button type="button" class="remove-file button">Remove</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
    
                            <button type="button" class="add-file button">Add File</button>
                        </div>
                    <?php endforeach; ?>
                </div>
    
                <button type="button" id="add-table" class="button button-primary">Add New Table</button>
                <input type="hidden" name="wp_fdt_files" id="wp_fdt_files">
                <input type="submit" class="button button-primary" value="Save Files">
            </form>
        </div>
    
        <script>
            document.querySelector('form').addEventListener('submit', function(event) {
                let tablesData = {};
                document.querySelectorAll('.file-table').forEach(function(table) {
                    let tableId = table.querySelector('.table-id').value.trim();
                    if (!tableId) return;

                    let files = [];
                    table.querySelectorAll('tbody tr').forEach(function(row) {
                        let fileData = {
                            name: row.querySelector('.file-name').value.trim(),
                            description: row.querySelector('.file-description').value.trim(),
                            url: row.querySelector('.file-url').value.trim(),
                            last_update: row.querySelector('.file-last-update').value.trim(),
                            size: row.querySelector('.file-size').value.trim()
                        };

                        if (fileData.url !== "") { // Only save if the URL is entered
                            files.push(fileData);
                        }
                    });

                    tablesData[tableId] = files;
              });

                console.log("Data Being Saved:", JSON.stringify(tablesData)); // Debugging
                document.getElementById('wp_fdt_files').value = JSON.stringify(tablesData);
            });

        </script>
    
        <style>
            .file-table {
                background: #fff;
                padding: 10px;
                margin-bottom: 20px;
                border: 1px solid #ccc;
            }
            .file-table h2 {
                margin-top: 0;
            }
            .file-table table {
                width: 100%;
            }
            .file-table input {
                width: 100%;
            }
            .file-table button {
                margin-top: 5px;
            }
        </style>
    
<?php
    
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wp_fdt_files'])) {
            check_admin_referer('wp_fdt_save_files');
    
            $tables = json_decode(stripslashes($_POST['wp_fdt_files']), true);
        
            if (!is_array($tables)) {
                wp_die(__('Invalid data format.'));
            }
    
            foreach ($tables as $table_id => $files) {
                if (!preg_match('/^[a-zA-Z0-9_-]+$/', $table_id)) {
                    wp_die(__('Invalid table ID.'));
                }
    
                foreach ($files as $key => $file) {
                    $files[$key]['name'] = sanitize_text_field($file['name']);
                    $files[$key]['description'] = sanitize_text_field($file['description']);
                    $files[$key]['url'] = esc_url_raw($file['url']);
                    $files[$key]['last_update'] = sanitize_text_field($file['last_update']);
                    $files[$key]['size'] = sanitize_text_field($file['size']);
                }
                $tables[$table_id] = $files;
            }
    
            global $wpdb;
$option_name = 'wp_fdt_file_tables';
$encoded_data = wp_json_encode($tables);

error_log("Final Encoded Data to Save (Direct DB): " . print_r($encoded_data, true));

// Directly update the database to avoid serialization
$wpdb->query(
    $wpdb->prepare(
        "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s",
        $encoded_data,
        $option_name
    )
);

// Verify what was actually saved
$saved_value = get_option('wp_fdt_file_tables');
error_log("After Save (Direct DB): " . print_r($saved_value, true));

        }
    
    }
    
}
