document.addEventListener('DOMContentLoaded', function () {
    var showHideKeyButton = document.querySelector('.itts-show-hide-key-button');
    var apiKeyInput = document.querySelector('input[name="itts_api_key"]');
    var speedInput = document.querySelector('input[name="itts_speed"]');
    var testApiButton = document.querySelector('#itts-test-api-button');
    var testApiResult = document.querySelector('.itts-test-api-result');
    var testAudioPlayer = document.querySelector('#itts-test-audio-player');
    var quotaInfo = document.querySelector('#itts-quota-info');

    if (showHideKeyButton) {
        showHideKeyButton.addEventListener('click', function () {
            if (apiKeyInput.getAttribute('type') === 'password') {
                apiKeyInput.setAttribute('type', 'text');
            } else {
                apiKeyInput.setAttribute('type', 'password');
            }
        });
    }

    if (speedInput) {
        speedInput.addEventListener('input', function () {
            var speedValue = this.value;
            this.nextElementSibling.textContent = speedValue;
        });
    }

    if (testApiButton) {
        testApiButton.addEventListener('click', function () {
            testApiResult.textContent = 'Testing API settings...';
            quotaInfo.textContent = "";
            testAudioPlayer.style.display = 'none';
            var xhr = new XMLHttpRequest();
            xhr.open('POST', itts_admin_ajax_object.ajax_url);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    console.log("done")
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (quotaInfo) {
                            const remaining = response.remaining; 
                            const limit = response.limit; 
                            if (limit && remaining) {
                                quotaInfo.textContent = `Quota: remaining ${remaining} symbols from ${limit}`;
                            }
                        }
                        if (response && response.success) {
                            testApiResult.textContent = 'Success! You can start adding [itts_audio] shortcode to your posts.';
                            testAudioPlayer.setAttribute('src', response.data);
                            testAudioPlayer.style.display = 'block';
                            testAudioPlayer.play();
                        } else {
                            testApiResult.textContent = 'Failed. Response: ' + response.error;
                            testAudioPlayer.style.display = 'none';
                        }
                    } else {
                        testApiResult.textContent = 'API test failed. Please check your API settings.';
                    }
                }
            };
            var data = new URLSearchParams({
                action: 'itts_test_api',
                voice: document.querySelector('#itts_voice').value,
                speed: document.querySelector('#itts_speed').value,
                apiKey: document.querySelector('#itts_api_key').value
            });
            xhr.send(data);
        });
    }
});
