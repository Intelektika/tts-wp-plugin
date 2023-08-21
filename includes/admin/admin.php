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
    register_setting('itts-settings-group', 'itts_api_key', array('type' => 'string', 'default' => ''));
    register_setting('itts-settings-group', 'itts_voice', array('type' => 'string', 'default' => 'astra'));
    register_setting('itts-settings-group', 'itts_speed', array('type' => 'number', 'default' => 1));
}
