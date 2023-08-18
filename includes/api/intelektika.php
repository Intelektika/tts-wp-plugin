<?php
class IntelektikaTTSAPI
{
    private $key;
    private $voice;
    private $speed;

    // Constructor
    public function __construct($key, $voice, $speed)
    {
        $this->key = $key;
        $this->voice = $voice;
        $this->speed = $speed;
    }

    public function generateAudio($post_id)
    {
        $post = get_post($post_id);
        $text = $post->post_content;

        if (!has_shortcode($text, 'text_to_speech')) {
            return;
        }

        // Log the post ID
        error_log('Synthesizing text for post ID: ' . $post_id);
        $text_without_shortcodes = preg_replace('/\[.*?\]/', '', $text);

        error_log('Post text: ' . $text_without_shortcodes);

        // Make API request to Google Text-to-Speech API
        $url = 'https://sinteze.intelektika.lt/synthesis.service/prod/synthesize';
        $data = array(
            "text" => $text_without_shortcodes,
            "outputFormat" => "mp3",
            "outputTextFormat" => "none",
            "speed" => $this->speed,
            "voice" => $this->voice
        );
        $response = wp_safe_remote_post(
            $url,
            array(
                'headers' => array('Content-Type' => 'application/json'),
                'body' => wp_json_encode($data)
            )
        );

        if (is_wp_error($response)) {
            error_log('Error synthesizing text.');
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        error_log('Got: ' . wp_json_encode($data));
        return base64_decode($data->audioAsString);
    }
}