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
});
