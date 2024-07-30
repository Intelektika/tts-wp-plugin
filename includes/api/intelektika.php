<?php

require_once plugin_dir_path(__FILE__) . './../consts.php';

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

        if (!has_shortcode($text, ITTS_SHORTCODE)) {
            error_log('Skip (no shortcode) synthesizing text for post ID: ' . $post_id);
            return [];
        }
        error_log('Synthesizing text for post ID: ' . $post_id);
        $text_without_shortcodes = preg_replace('/\[.*?\]/', '', $text);

        return $this->generateForText($text_without_shortcodes);
    }

    public function generateForText($text)
    {
        error_log('$this->voice : ' . $this->voice);
        error_log('$this->speed : ' . $this->speed);
        $url = 'https://sinteze.intelektika.lt/synthesis.service/prod/synthesize';
        $data = array(
            "text" => $text,
            "outputFormat" => "mp3",
            "outputTextFormat" => "none",
            "speed" => $this->speed,
        );
        if (!empty($this->voice)) {
            $data["voice"] = $this->voice;
        }

        $headers = array('Content-Type' => 'application/json');

        if (!empty($this->key)) {
            $headers['Authorization'] = 'Key ' . $this->key;
        }
        $timeout = 45;
        error_log('timeout set  : ' . $timeout);
        $response = wp_safe_remote_post(
            $url,
            array(
                'headers' => $headers,
                'body' => wp_json_encode($data),
                'timeout' => $timeout, 
            )
        );

        if (is_wp_error($response)) {
            return ['error' => 'Error synthesizing text.'];
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            $remaining = wp_remote_retrieve_header($response, "x-rate-limit-remaining");
            $limit = wp_remote_retrieve_header($response, "x-rate-limit-limit");
            error_log('Response : ' . $response_code);
            if ($response_code === 200) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body);
                return ['result' => $data->audioAsString, 'error' => null, 'remaining' => $remaining, 'limit' => $limit];
            } else {
                $body = wp_remote_retrieve_body($response);
                error_log('Got error: ' . $response_code . '. Body: ' . $body);
                return ['error' => 'TTS Error. Msg:' . $body, 'remaining' => $remaining, 'limit' => $limit];
            }
        }
    }
}