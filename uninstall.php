<?php
/**
 * Deactivation hook.
 */
function itts_deactivate()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'int_tts_deactivate');
