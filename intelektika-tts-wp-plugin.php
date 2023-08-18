<?php
/*
Plugin Name:       Intelektika Text-to-Speech Plugin
Description:       Convert text of a post to spoken audio using the Intelektika TTS API.
Version:           1.0.0
Author:            Airenas Vaiciunas
Plugin URI:        https://github.com/Intelektika/tts-wp-plugin/
Requires at least: 5.2
Requires PHP:      7.2
License:           GPL v2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Update URI:        https://github.com/Intelektika/tts-wp-plugin/
*/
include_once __DIR__ . './../action-scheduler/action-scheduler.php';


function itts_check_requirements()
{
    if (is_admin() && current_user_can('activate_plugins') && !is_plugin_active('action-scheduler/action-scheduler.php')) {
        add_action('admin_notices', 'itts_action_scheduler_plugin_notice');
        deactivate_plugins(plugin_basename(__FILE__));
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}
function itts_action_scheduler_plugin_notice()
{
    ?>
    <div class="error">
        <p>Sorry, but <strong>Intelektika TTS Plugin</strong> requires the <strong>Action Scheduler</strong> plugin to be
            installed and active.</p>
    </div>
    <?php
}
add_action('admin_init', 'itts_check_requirements');


// Enqueue scripts and styles
function tts_enqueue_scripts()
{
    wp_enqueue_script('jquery'); // Enqueue jQuery if not already enqueued
    wp_enqueue_script('tts-script', plugin_dir_url(__FILE__) . 'js/tts-script.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'tts_enqueue_scripts');
function tts_localize_scripts()
{
    wp_localize_script('tts-script', 'tts_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'tts_localize_scripts');


// Shortcode for displaying the TTS form
function tts_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'post_id' => get_the_ID(),
            // Default to the current post's ID
        ),
        $atts
    );

    $audio_url = get_cached_audio_url($atts['post_id']);

    ob_start();
    ?>
    <div id="tts-form">
        <?php if ($audio_url): ?>
            <audio id="audio-player" controls>
                <source src="<?php echo esc_attr($audio_url); ?>" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        <?php else: ?>
            <button id="synthesize-button" data-post-id="<?php echo esc_attr($atts['post_id']); ?>">Synthesize</button>
            <audio id="audio-player" controls style="display: none;"></audio>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('text_to_speech', 'tts_shortcode');


// AJAX callback for synthesizing text
function synthesize_text_callback()
{

    error_log('Call synthesize_text_callback');

    $text = sanitize_text_field($_POST['text']);
    $post_id = intval($_POST['post_id']);
    $api_key = 'YOUR_GOOGLE_TTS_API_KEY'; // Replace with your API key

    $upload_dir = wp_upload_dir();
    $cache_dir = trailingslashit($upload_dir['basedir']) . 'tts_cache/';
    if (!file_exists($cache_dir)) {
        mkdir($cache_dir);
    }

    $cache_file = $cache_dir . 'post_' . $post_id . '.mp3';

    error_log('Looking for: ' . $cache_file);
    if (file_exists($cache_file)) {
        error_log('got cache');
        $cache_url = trailingslashit($upload_dir['baseurl']) . 'tts_cache/post_' . $post_id . '.mp3';
        wp_send_json_success($cache_url);
    }

    error_log('Received text for synthesis: ' . $text);

    // Get the post content
    $post = get_post($post_id);
    $text_post = $post->post_content;

    // Log the post ID
    error_log('Synthesizing text for post ID: ' . $post_id);
    $text_without_shortcodes = preg_replace('/\[.*?\]/', '', $text_post);

    error_log('Post text: ' . $text_without_shortcodes);

    $api_key = get_option('tts_api_key');
    $voice = get_option('tts_voice');
    $speed = floatval(get_option('tts_speed'));

    // Make API request to Google Text-to-Speech API
    $url = 'https://sinteze.intelektika.lt/synthesis.service/prod/synthesize';
    $data = array(
        "text" => $text_without_shortcodes,
        "outputFormat" => "mp3",
        "outputTextFormat" => "none",
        "speed" => $speed,
        "voice" => $voice
    );

    $headers = array('Content-Type: application/json');
    if (!empty($api_key)) {
        $headers[] = 'Authorization: Key ' . $api_key;
    }

    $response = wp_safe_remote_post(
        $url,
        array(
            'headers' => $headers,
            'body' => wp_json_encode($data)
        )
    );

    if (is_wp_error($response)) {
        wp_send_json_error('Error synthesizing text.');
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);
    error_log('Got: ' . wp_json_encode($data));

    if (!empty($data->audioAsString)) {
        $audio_url = 'data:audio/mpeg;base64,' . $data->audioAsString;
        error_log('Generated audio URL: ' . $audio_url);
        $audio_content = base64_decode($data->audioAsString);
        file_put_contents($cache_file, $audio_content);

        $cache_url = trailingslashit($upload_dir['baseurl']) . 'tts_cache/post_' . $post_id . '.mp3';
        wp_send_json_success($cache_url);
    } else {
        error_log('Error synthesizing text.');
        wp_send_json_error('Error synthesizing text.');
    }
}
add_action('wp_ajax_synthesize_text', 'synthesize_text_callback');
add_action('wp_ajax_nopriv_synthesize_text', 'synthesize_text_callback');

// Add a settings page to the WordPress admin menu
require_once plugin_dir_path(__FILE__) . 'includes/admin/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/settings-page.php';
add_action('admin_menu', 'itts_settings_page');
add_action('admin_enqueue_scripts', 'itts_enqueue_admin_scripts');
add_action('admin_init', 'itts_register_settings');

function generate_audio_on_post_save($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return; // Ignore autosaves

    // Check post type if needed
    $post_type = get_post_type($post_id);
    if ($post_type !== 'post')
        return;

    // Generate audio for the post's content
    $post = get_post($post_id);
    $text = $post->post_content;

    // Call your audio generation function here
    enqueue_tts_post($text, $post_id);
}

add_action('intelektika_tts_schedule_task', 'intelektika_tts_generate_audio', 10, 1);

function intelektika_tts_enqueue_tts_post($post_id)
{
    as_enqueue_async_action('intelektika_tts_schedule_task', [$post_id], 'intelektika_tts_plugin', true, 1);
}

add_action('save_post', 'enqueue_tts_post');

function regenerate_audio_on_post_update($post_id, $post_after)
{
    enqueue_tts_post($post_id);
}
add_action('edit_post', 'regenerate_audio_on_post_update', 10, 2);