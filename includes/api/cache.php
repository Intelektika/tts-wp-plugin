<?php

class IntelektikaTTSFileCache
{
    const CACHE_DIR = "itts_generated_audio";
    private $generator;

    public function __construct($generator)
    {
        $this->generator = $generator;
        error_log('created  IntelektikaTTSFileCache');
    }

    public function generateSaveAudio($post_id)
    {
        error_log('generateSaveAudio audio: ' . $post_id);
        $file_name = IntelektikaTTSFileCache::getFullFileName($post_id);
        $dir = dirname($file_name);
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        IntelektikaTTSFileCache::cleanCache($file_name);
        $result = $this->generator->generateAudio($post_id);
        error_log('got result');
        if ($result['error']) {
            error_log('Error: ' . $result['error']);
        } else {
            file_put_contents($file_name, base64_decode($result['result']));
            error_log('Saved file: ' . $file_name);
        }
    }

    static function getFullFileName($post_id)
    {
        $upload_dir = wp_upload_dir();
        return trailingslashit($upload_dir['basedir']) . IntelektikaTTSFileCache::getFileName($post_id);
    }

    static function cleanCache($file_name)
    {
        if (file_exists($file_name)) {
            if (unlink($file_name)) {
                error_log('Deleted: ' . $file_name);
            } else {
                error_log('Cannot delete : ' . $file_name);
            }
        }
    }

    static function getFileName($post_id)
    {
        return IntelektikaTTSFileCache::CACHE_DIR . '/post_' . $post_id . '.mp3';
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
}