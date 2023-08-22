document.addEventListener('DOMContentLoaded', function () {
    var playAudioButton = document.getElementById('itts-play-audio-button');
    var audioPlayer = document.getElementById('itts-audio-player');
    if (playAudioButton && audioPlayer) {
        playAudioButton.addEventListener('click', function () {
            audioPlayer.style.display = 'block';
            audioPlayer.play();
            this.style.display = 'none';
        });
    }
});
