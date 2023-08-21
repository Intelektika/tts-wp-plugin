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

        return $this->generateForText($text_without_shortcodes);
    }

    public function generateForText($text)
    {
        // Make API request to Google Text-to-Speech API
        $url = 'https://sinteze.intelektika.lt/synthesis.service/prod/synthesize';
        $data = array(
            "text" => $text,
            "outputFormat" => "mp3",
            "outputTextFormat" => "none",
            "speed" => $this->speed,
            "voice" => $this->voice
        );
        $headers = array('Content-Type' => 'application/json');

        if (!empty($this->key)) {
            $headers['Authorization'] = 'Key ' . $this->key;
        }
        $response = wp_safe_remote_post(
            $url,
            array(
                'headers' => $headers,
                'body' => wp_json_encode($data)
            )
        );

        if (is_wp_error($response)) {
            return ['error' => 'Error synthesizing text.'];
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            error_log('Response : ' . $response_code);
            if ($response_code === 200) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body);
                return ['result' => $data->audioAsString, 'error' => null];
            } else {
                $body = wp_remote_retrieve_body($response);
                error_log('Got error: ' . $response_code . '. Body: ' . $body);
                return ['error' => 'TTS Error. Msg:' . $body];
            }
        }
    }
}