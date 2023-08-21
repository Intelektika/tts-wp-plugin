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
    $error_msg = null;
    if (! $audio_url) {
        $error_msg = IntelektikaTTSFileCache::getErrorMsg($atts['post_id']);
    }
    error_log("audio ". $audio_url);

    ob_start();
    ?>
    <div id="itts-form">
        <?php if ($audio_url): ?>
            <audio id="audio-player" controls>
                <source src="<?php echo esc_attr($audio_url); ?>" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        <?php elseif (is_admin() && $error_msg): ?>
            <dix id="itts-error=msg"><?php echo esc_attr($atts['post_id']); ?>"</div>
        <?php  endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
