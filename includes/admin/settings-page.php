<?php

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
                            <input type="password" name="itts_api_key"
                                value="<?php echo esc_attr(get_option('itts_api_key')); ?>" />
                            <button type="button" class="itts-show-hide-key-button">Show/Hide Key</button>
                        </div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Voice</th>
                    <td>
                        <input type="text" name="itts_voice" value="<?php echo esc_attr(get_option('itts_voice')); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Speed</th>
                    <td>
                        <input type="range" name="itts_speed" min="0.5" max="2" step="0.1"
                            value="<?php echo esc_attr(get_option('itts_speed', 1)); ?>" />
                        <span class="speed-value">
                            <?php echo esc_html(get_option('itts_speed', 1)); ?>
                        </span>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function itts_enqueue_admin_scripts()
{
    wp_enqueue_script('itts-admin-script', plugin_dir_url(__FILE__) . 'js/tts-admin.js', array('jquery'), '1.0', true);
}
