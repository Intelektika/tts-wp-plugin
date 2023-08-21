<?php

function itts_get_voices()
{
    $response = wp_safe_remote_get('https://sinteze.intelektika.lt/synthesis.data/models.json');
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
    return array(); // Return an empty array on error
}

function itts_settings_form()
{
    ?>
    <div class="wrap">
        <h2>Intelektika Text-to-Speech Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('itts-settings-group'); ?>
            <?php do_settings_sections('itts-settings'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">TTS API Key</th>
                    <td>
                        <div class="api-key-container">
                            <input type="password" id="itts_api_key" name="itts_api_key"
                                value="<?php echo esc_attr(get_option('itts_api_key')); ?>" />
                            <button type="button" class="itts-show-hide-key-button">Show/Hide Key</button>
                        </div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Voice</th>
                    <td>
                        <select id="itts_voice" name="itts_voice">
                            <option value="">Select a voice</option>
                            <?php
                            $voices = itts_get_voices();
                            $current = get_option('itts_voice');
                            foreach ($voices as $voice) {
                                $voice_id = sanitize_key($voice['id']);
                                $voice_name = esc_html($voice['name']);
                                $selected = selected($voice_id, $current, false);
                                echo '<option value="' . $voice_id . '" ' . $selected . '>' . $voice_name . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Speed</th>
                    <td>
                        <input type="range" id="itts_speed" name="itts_speed" min="0.5" max="2" step="0.1"
                            value="<?php echo esc_attr(get_option('itts_speed', 1)); ?>" />
                        <span class="speed-value">
                            <?php echo esc_html(get_option('itts_speed', 1)); ?>
                        </span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"></th>
                    <td>
                        <button type="button" id="itts-test-api-button" class="button">Test TTS</button>
                        <p class="itts-test-api-result"></p>
                        <audio id="itts-test-audio-player" controls style="display: none;"></audio>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function itts_admin_localize_scripts()
{
    wp_localize_script('itts-admin-script', 'itts_admin_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
function itts_enqueue_admin_scripts()
{
    wp_enqueue_script('jquery'); // Enqueue jQuery if not already enqueued
    wp_enqueue_script('itts-admin-script', plugin_dir_url(__FILE__) . 'js/tts-admin.js', array('jquery'), '1.0', true);
}