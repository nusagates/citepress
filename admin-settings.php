<?php
// Adds the CitePress settings menu to the WordPress admin
function cpress_settings_menu() {
    add_options_page(
        esc_html__('CitePress Settings', 'citepress'),
        esc_html__('CitePress', 'citepress'),
        'manage_options',
        'cpress-settings',
        'cpress_settings_page'
    );
}
add_action('admin_menu', 'cpress_settings_menu');

// Outputs the CitePress settings page HTML in the admin area
function cpress_settings_page() {
    $styles = array(
        'apa'       => 'APA',
        'mla'       => 'MLA',
        'chicago'   => 'Chicago',
        'ieee'      => 'IEEE',
        'harvard'   => 'Harvard',
        'vancouver' => 'Vancouver',
        'asa'       => 'ASA',
        'acs'       => 'ACS',
        'acm'       => 'ACM',
    );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('CitePress Settings', 'citepress'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('cpress-settings-group');
            do_settings_sections('cpress-settings-group');
            ?>
            <table class="form-table">
                <tr class="align-top">
                    <th scope="row"><?php esc_html_e('Access Label', 'citepress'); ?></th>
                    <td><input type="text" name="cpress_accessed_label"
                               value="<?php echo esc_attr(get_option('cpress_accessed_label', 'Accessed on')); ?>"
                               class="regular-text"/></td>
                </tr>
                <tr class="align-top">
                    <th scope="row"><?php esc_html_e('Access Date Format', 'citepress'); ?></th>
                    <td><input type="text" name="cpress_date_format"
                               value="<?php echo esc_attr(get_option('cpress_date_format', 'F j, Y \a\t H:i')); ?>"
                               class="regular-text"/></td>
                </tr>
                <tr class="align-top">
                    <th scope="row"><?php esc_html_e('Citation Box Label', 'citepress'); ?></th>
                    <td><input type="text" name="cpress_box_title"
                               value="<?php echo esc_attr(get_option('cpress_box_title', 'Reference')); ?>"
                               class="regular-text"/></td>
                </tr>
                <tr class="align-top">
                    <th scope="row"><?php esc_html_e('Auto Placement', 'citepress'); ?></th>
                    <td>
                        <select name="cpress_auto_placement">
                            <option value="off" <?php selected(get_option('cpress_auto_placement', 'off'), 'off'); ?>>
                                <?php esc_html_e('Off', 'citepress'); ?>
                            </option>
                            <option value="before" <?php selected(get_option('cpress_auto_placement', 'off'), 'before'); ?>>
                                <?php esc_html_e('Before Article', 'citepress'); ?>
                            </option>
                            <option value="after" <?php selected(get_option('cpress_auto_placement', 'off'), 'after'); ?>>
                                <?php esc_html_e('After Article', 'citepress'); ?>
                            </option>
                            <option value="both" <?php selected(get_option('cpress_auto_placement', 'off'), 'both'); ?>>
                                <?php esc_html_e('Before and After', 'citepress'); ?>
                            </option>
                        </select>
                        <p class="description"><?php esc_html_e('Automatically display the citation box before, after, or both before and after the article.', 'citepress'); ?></p>
                    </td>
                </tr>
                <tr class="align-top">
                    <th scope="row"><?php esc_html_e('Default Auto Placement Style', 'citepress'); ?></th>
                    <td>
                        <select name="cpress_auto_placement_style">
                            <?php
                            $selected_style = get_option('cpress_auto_placement_style', 'apa');
                            foreach ($styles as $key => $label) {
                                printf(
                                    '<option value="%s"%s>%s</option>',
                                    esc_attr($key),
                                    selected($selected_style, $key, false),
                                    esc_html($label)
                                );
                            }
                            ?>
                        </select>
                        <p class="description"><?php esc_html_e('Select the citation style for auto placement.', 'citepress'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <div class="postbox" style="margin-top:2em;max-width:600px;">
            <h2 style="margin:0;padding:10px 15px;background:#f9f9f9;border-bottom:1px solid #ccc;">
                <?php esc_html_e('Manual Placement Shortcodes', 'citepress'); ?>
            </h2>
            <div style="padding:15px;">
                <p><?php esc_html_e('You can manually place the citation box anywhere in your post using these shortcodes:', 'citepress'); ?></p>
                <ul style="list-style:disc;padding-left:20px;">
                    <li><code>[citepress]</code> <span style="color:#888;"><?php esc_html_e('(Default: APA style)', 'citepress'); ?></span></li>
                    <li><code>[citepress style="mla"]</code></li>
                    <li><code>[citepress style="chicago"]</code></li>
                    <li><code>[citepress style="ieee"]</code></li>
                    <li><code>[citepress style="harvard"]</code></li>
                    <li><code>[citepress style="vancouver"]</code></li>
                    <li><code>[citepress style="asa"]</code></li>
                    <li><code>[citepress style="acs"]</code></li>
                    <li><code>[citepress style="acm"]</code></li>
                </ul>
                <p style="color:#888;"><?php esc_html_e('Place the shortcode in your post content where you want the citation box to appear.', 'citepress'); ?></p>
            </div>
        </div>
    </div>
    <?php
}

// Registers all CitePress settings with WordPress
function cpress_register_settings() {
    register_setting('cpress-settings-group', 'cpress_accessed_label', 'sanitize_text_field');
    register_setting('cpress-settings-group', 'cpress_date_format', 'sanitize_text_field');
    register_setting('cpress-settings-group', 'cpress_box_title', 'sanitize_text_field');
    register_setting('cpress-settings-group', 'cpress_auto_placement', 'sanitize_text_field');
    register_setting('cpress-settings-group', 'cpress_auto_placement_style', 'sanitize_text_field');
}
add_action('admin_init', 'cpress_register_settings');