<?php

class IntelektikaTTSFileCache
{
    const CACHE_DIR = "itts_generated_audio";
    const INFO_DIR = "itts_info";
    private $generator;

    public function __construct($generator)
    {
        $this->generator = $generator;
        error_log('created  IntelektikaTTSFileCache');
    }

    public function generateSaveAudio($post_id)
    {
        error_log('generateSaveAudio audio: ' . $post_id);
        $generating = get_transient(IntelektikaTTSFileCache::lockKey($post_id));
        if ($generating) {
            error_log('Already generating: ' . $post_id);
            return;
        }
        
        set_transient(IntelektikaTTSFileCache::lockKey($post_id), true, 60);
        delete_transient(IntelektikaTTSFileCache::waitingKey($post_id));

        IntelektikaTTSFileCache::cleanCache($post_id);
        $result = $this->generator->generateAudio($post_id);
        error_log('got result');
        if ($result['error']) {
            error_log('Error: ' . $result['error']);
            IntelektikaTTSFileCache::saveError($post_id, $result['error']);
        }
        else if ($result['result']) {
            $file_name = IntelektikaTTSFileCache::getFullFileName($post_id);
            $dir = dirname($file_name);
            if (!file_exists($dir)) {
                mkdir($dir);
            }
            file_put_contents($file_name, base64_decode($result['result']));
            error_log('Saved file: ' . $file_name);
        } else {
            error_log('No audio file created for ' . $post_id);
        }
        delete_transient(IntelektikaTTSFileCache::lockKey($post_id));
    }

    static function saveError($post_id, $error)
    {
        $info_file_name = IntelektikaTTSFileCache::getInfoFileName($post_id);
        $dir = dirname($info_file_name);
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        file_put_contents($info_file_name, $error);
        error_log('Saved file: ' . $info_file_name);
    }

    static function getFullFileName($post_id)
    {
        $upload_dir = wp_upload_dir();
        return trailingslashit($upload_dir['basedir']) . IntelektikaTTSFileCache::getFileName($post_id);
    }

    static function getInfoFileName($post_id)
    {
        return trailingslashit(ITTS_PLUGIN_PATH) . IntelektikaTTSFileCache::INFO_DIR . '/post_' . $post_id . '.info';
    }

    static function cleanCache($post_id)
    {
        $file_name = IntelektikaTTSFileCache::getFullFileName($post_id);
        if (file_exists($file_name)) {
            if (unlink($file_name)) {
                error_log('Deleted: ' . $file_name);
            } else {
                error_log('Cannot delete : ' . $file_name);
            }
        }
        $info_file_name = IntelektikaTTSFileCache::getInfoFileName($post_id);
        if (file_exists($info_file_name)) {
            if (unlink($info_file_name)) {
                error_log('Deleted: ' . $info_file_name);
            } else {
                error_log('Cannot delete : ' . $info_file_name);
            }
        }
    }

    static function getFileName($post_id)
    {
        return IntelektikaTTSFileCache::CACHE_DIR . '/post_' . $post_id . '.mp3';
    }

    static function lockKey($post_id)
    {
        return 'itts-lock-post-' . $post_id;
    }

    static function waitingKey($post_id)
    {
        return 'itts-generating-post-' . $post_id;
    }

    static function markWaiting($post_id)
    {
        set_transient(IntelektikaTTSFileCache::waitingKey($post_id), "waiting", 3*60);
    }

    static function getAudioURL($post_id)
    {
        $file_name = IntelektikaTTSFileCache::getFullFileName($post_id);
        if (file_exists($file_name)) {
            $upload_dir = wp_upload_dir();
            return trailingslashit($upload_dir['baseurl']) . IntelektikaTTSFileCache::getFileName($post_id);
        }
        return false;
    }

    static function getMsg($post_id)
    {
        $waiting = get_transient(IntelektikaTTSFileCache::waitingKey($post_id));
        if ($waiting) {
            return "Waiting for synthesis ...";
        }
        $generating = get_transient(IntelektikaTTSFileCache::lockKey($post_id));
        if ($generating) {
            return "Generating audio ...";
        }
        $file_name = IntelektikaTTSFileCache::getInfoFileName($post_id);
        if (file_exists($file_name)) {
            $file_contents = file_get_contents($file_name);
            $sanitized_contents = esc_html($file_contents);
            return $sanitized_contents;
        }
        return false;
    }
}