<?php
/**
 * Plugin Name: CitePress – Automatic Citation Generator
 * Description: Display a reference (bibliography) from the current post in multiple citation formats.
 * Version: 1.7
 * Author: Ahmad Budairi
 * Author URI: https://nusagates.co.id/author/cakbud/
 * Plugin URI: https://nusagates.co.id
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: citepress
 * Domain Path: /languages
 * Author Email: budairi.connect@gmail.com
 */

// Load admin settings
require_once plugin_dir_path(__FILE__) . 'admin-settings.php';

// Function to retrieve post metadata
function cpress_get_post_metadata($post_id) {
    $author_full = get_the_author_meta('display_name', get_post_field('post_author', $post_id));
    $author_parts = explode(' ', $author_full);
    $last_name = array_pop($author_parts);
    $first_names = implode(' ', $author_parts);
    $formatted_author = $last_name . ', ' . $first_names;

    $accessed_label = get_option('cpress_accessed_label', 'Accessed on');
    $date_format = get_option('cpress_date_format', 'F j, Y \a\t H:i');

    return array(
        'author'         => $formatted_author,
        'title'          => get_the_title($post_id),
        'year'           => get_the_date('Y', $post_id),
        'site_name'      => get_bloginfo('name'),
        'url'            => get_permalink($post_id),
        'accessed'       => date_i18n($date_format),
        'accessed_label' => $accessed_label
    );
}

// Function to format the citation string
function cpress_format_reference($post_id, $style = 'apa') {
    $data = cpress_get_post_metadata($post_id);

    switch (strtolower($style)) {
        case 'acm':
            return sprintf(
                '%s. %s. <em>%s</em>. %s %s',
                esc_html($data['author']),
                esc_html($data['year']),
                esc_html($data['title']),
                esc_html($data['accessed_label']),
                esc_url($data['url'])
            );
        case 'acs':
            return sprintf(
                '%s. %s, <em>%s</em>. %s, %s.',
                esc_html($data['author']),
                esc_html($data['year']),
                esc_html($data['title']),
                esc_html($data['accessed_label']),
                esc_url($data['url'])
            );
        case 'harvard':
            return sprintf(
                '%s (%s) <em>%s</em>, %s. Available at: %s (%s: %s)',
                esc_html($data['author']),
                esc_html($data['year']),
                esc_html($data['title']),
                esc_html($data['site_name']),
                esc_url($data['url']),
                esc_html($data['accessed_label']),
                esc_html($data['accessed'])
            );
        case 'mla':
            return sprintf(
                '%s. "<em>%s</em>." %s, %s. Web. %s %s.',
                esc_html($data['author']),
                esc_html($data['title']),
                esc_html($data['site_name']),
                esc_html($data['year']),
                esc_html($data['accessed_label']),
                esc_html($data['accessed'])
            );
        case 'chicago':
            return sprintf(
                '%s. "%s." <em>%s</em>. %s. %s %s. %s.',
                esc_html($data['author']),
                esc_html($data['title']),
                esc_html($data['site_name']),
                esc_html($data['year']),
                esc_html($data['accessed_label']),
                esc_html($data['accessed']),
                esc_url($data['url'])
            );
        case 'vancouver':
            return sprintf(
                '%s. %s. %s. Available from: %s. %s %s.',
                esc_html($data['author']),
                esc_html($data['title']),
                esc_html($data['site_name']),
                esc_url($data['url']),
                esc_html($data['accessed_label']),
                esc_html($data['accessed'])
            );
        case 'asa':
            return sprintf(
                '%s. %s. "%s." %s. %s %s.',
                esc_html($data['author']),
                esc_html($data['year']),
                esc_html($data['title']),
                esc_html($data['site_name']),
                esc_html($data['accessed_label']),
                esc_html($data['accessed'])
            );
        case 'ieee':
            return sprintf(
                '%s. "%s," <em>%s</em>, %s. [Online]. Available: %s. [%s: %s]',
                esc_html($data['author']),
                esc_html($data['title']),
                esc_html($data['site_name']),
                esc_html($data['year']),
                esc_url($data['url']),
                esc_html($data['accessed_label']),
                esc_html($data['accessed'])
            );
        case 'apa':
        default:
            return sprintf(
                '%s. (%s). <em>%s</em>. %s. %s (%s %s)',
                esc_html($data['author']),
                esc_html($data['year']),
                esc_html($data['title']),
                esc_html($data['site_name']),
                esc_url($data['url']),
                esc_html($data['accessed_label']),
                esc_html($data['accessed'])
            );
    }
}

// Shortcode to display the citation
function cpress_citepress_shortcode($atts) {
    if (!is_singular('post')) return '';

    $atts = shortcode_atts(array(
        'style' => 'apa'
    ), $atts);

    $post_id = get_the_ID();
    $reference = cpress_format_reference($post_id, $atts['style']);
    $box_title = get_option('cpress_box_title', 'Reference');

    ob_start();
    echo '<div class="citepress-box">';
    echo '<div class="citepress-box-heading"><strong>' . esc_html($box_title) . '</strong></div>';
    echo '<div class="citepress-box-body">' . wp_kses_post($reference) . '</div>';
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('citepress', 'cpress_citepress_shortcode');

// Enqueue plugin styles
function cpress_enqueue_styles() {
    wp_enqueue_style(
        'citepress-style',
        plugins_url('css/style.css', __FILE__),
        array(),
        '1.6'
    );
}
add_action('wp_enqueue_scripts', 'cpress_enqueue_styles');

// Auto placement of citation box with style selection
function cpress_auto_placement_output($content) {
    if (!is_singular('post')) return $content;
    $placement = get_option('cpress_auto_placement', 'off');
    $style = get_option('cpress_auto_placement_style', 'apa');
    $reference = cpress_citepress_shortcode(array('style' => $style));

    if ($placement === 'before') {
        return $reference . $content;
    } elseif ($placement === 'after') {
        return $content . $reference;
    } elseif ($placement === 'both') {
        return $reference . $content . $reference;
    }
    return $content;
}
add_filter('the_content', 'cpress_auto_placement_output');