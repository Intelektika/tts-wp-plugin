<?php
function itts_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'post_id' => get_the_ID(),
        ),
        $atts
    );

    $audio_url = IntelektikaTTSFileCache::getAudioURL($atts['post_id']);
    $msg = null;
    $edit_mode = current_user_can('edit_post', $atts['post_id']);
    if (!$audio_url && $edit_mode) {
        $msg = IntelektikaTTSFileCache::getMsg($atts['post_id']);
        error_log("got msg: " . $msg);
    }
    error_log("audio " . $audio_url);

    ob_start();
    ?>
    <div id="itts-form">
        <?php if ($audio_url): ?>
            <audio id="audio-player" controls>
                <source src="<?php echo esc_attr($audio_url); ?>" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        <?php elseif ($edit_mode): ?>
            <div class="itts-admin-message">
                <span>Here you should see an audio component.</span><br>
                <span>Please wait for some seconds if you just saved the article.</span><br>
                <span>Please note this message won't apear in a view mode!</span><br>
                <span class="itts-admin-error">
                    <?php echo $msg; ?>
                </span>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}