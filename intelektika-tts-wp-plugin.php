<?php
/*
Plugin Name:       Intelektika Text-to-Speech Plugin
Description:       Convert text of a post to spoken audio using the Intelektika TTS API.
Version:           1.0.4
Author:            Intelektika
Plugin URI:        https://github.com/Intelektika/tts-wp-plugin/
Requires at least: 5.2
Requires PHP:      7.2
License:           GPL v2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Update URI:        https://github.com/Intelektika/tts-wp-plugin/
*/

define( 'ITTS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
require_once plugin_dir_path(__FILE__) . 'includes/consts.php';

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


// Shortcode for displaying the TTS form
require_once plugin_dir_path(__FILE__) . 'includes/api/intelektika.php';
require_once plugin_dir_path(__FILE__) . 'includes/api/cache.php';
require_once plugin_dir_path(__FILE__) . 'includes/public/public.php';

add_shortcode(ITTS_SHORTCODE, 'itts_shortcode');
function itts_enqueue_styles()
{
    wp_enqueue_style('itts-style', plugin_dir_url(__FILE__) . 'includes/public/css/itts-styles.css');
}
add_action('wp_enqueue_scripts', 'itts_enqueue_styles');


// Add a settings page to the WordPress admin menu
require_once plugin_dir_path(__FILE__) . 'includes/admin/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin/settings-page.php';
add_action('admin_menu', 'itts_settings_page');
add_action('admin_enqueue_scripts', 'itts_enqueue_admin_scripts');
add_action('admin_enqueue_scripts', 'itts_admin_localize_scripts');
add_action('admin_init', 'itts_register_settings');
add_action('wp_ajax_itts_test_api', 'itts_test_api_handler');

function itts_test_api_handler()
{
    error_log('Invoke itts_test_api_handler');
    try {
        $api_key = sanitize_text_field($_POST['apiKey']);
        $voice = sanitize_text_field($_POST['voice']);
        $speed = floatval($_POST['speed']);
        $generator = new IntelektikaTTSAPI($api_key, $voice, $speed);
        $result = $generator->generateForText("Sveiki! Jūs klausote sugeneruotą tekstą.");
        error_log('got result');
        if ( isset( $result["error"] ) ) {
            error_log('Error: ' . $result['error']);
        } 
        wp_send_json(map_response($result));
    } catch (Exception $e) {
        error_log($e->getMessage());
        wp_send_json_error('Error synthesizing text.');
    }
}

function map_response($resp)
{
    $result = array( 'success' => false );
    if ( isset( $resp["result"] ) ) {
        $result['data'] = 'data:audio/mpeg;base64,' . $resp['result'];
        $result['success'] = true;
    }
    if ( isset( $resp["error"] ) ) {
        $result['error'] = $resp["error"];
        $result['success'] = false;
    } 
    if ( isset( $resp["limit"] ) ) {
        $result['limit'] = $resp['limit'];
    }
    if ( isset( $resp["remaining"] ) ) {
        $result ['remaining'] = $resp['remaining'];
    }
    return $result; 
}

function itts_generate_audio($post_id)
{
    // Generate audio for the post's content
    error_log('Generating audio: ' . $post_id);
    try {
        $api_key = get_option('itts_api_key');
        $voice = get_option('itts_voice');
        $speed = floatval(get_option('itts_speed'));
        $generator = new IntelektikaTTSAPI($api_key, $voice, $speed);
        $cacher = new IntelektikaTTSFileCache($generator);
        $cacher->generateSaveAudio($post_id);
        error_log('Generating audio done: ' . $post_id);
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

add_action('itts_schedule_task', 'itts_generate_audio', 10, 1);

function itts_enqueue_post($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return; // Ignore autosaves

    // Check post type if needed
    $post_type = get_post_type($post_id);
    if ($post_type !== 'post')
        return;

    error_log('Schedule generating audio: ' . $post_id);
    IntelektikaTTSFileCache::enqueueJob($post_id);
}


function itts_regenerate_post($post_id, $post_after)
{
    itts_enqueue_post($post_id);
}

add_action('save_post', 'itts_enqueue_post');
add_action('edit_post', 'itts_regenerate_post', 10, 2);