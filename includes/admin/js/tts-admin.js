jQuery(document).ready(function($) {
    $('.itts-show-hide-key-button').click(function() {
        var apiKeyInput = $(this).prev('input[name="itts_api_key"]');
        if (apiKeyInput.attr('type') === 'password') {
            apiKeyInput.attr('type', 'text');
        } else {
            apiKeyInput.attr('type', 'password');
        }
    });

    $('input[name="itts_speed"]').on('input', function() {
        var speedValue = $(this).val();
        $(this).next('.speed-value').text(speedValue);
    });

    $('#itts-test-api-button').click(function() {
        $('.itts-test-api-result').text('Testing API settings...');
        $('#itts-test-audio-player').hide();
        $.ajax({
            type: 'POST',
            url: itts_admin_ajax_object.ajax_url, 
            data: {
                action: 'itts_test_api',
                voice: $('#itts_voice').val(),
                speed: $('#itts_speed').val(),
                apiKey: $('#itts_api_key').val(),
            },
            success: function(response) {
                console.log(response)
                if (response && response.success) {
                    $('.itts-test-api-result').text('Success! You can start adding [itts_audio] shortcode to your posts.');
                    $('#itts-test-audio-player').attr('src', response.data).show().get(0).play();
                } else {
                    $('.itts-test-api-result').text('Failed. Response: ' + response.data);
                    $('#itts-test-audio-player').hide();
                }   
            },
            error: function() {
                $('.itts-test-api-result').text('API test failed. Please check your API settings.');
            }
        });
    });
});
