<?php

function itts_settings_page()
{
    add_options_page(
        'Intelektika Text-to-Speech Settings',
        'Text-to-Speech',
        'manage_options',
        'itts-settings',
        'itts_settings_form'
    );
}


function itts_register_settings()
{
    register_setting('itts-settings-group', 'itts_api_key');
    register_setting('itts-settings-group', 'itts_voice');
    register_setting('itts-settings-group', 'itts_speed');
}

