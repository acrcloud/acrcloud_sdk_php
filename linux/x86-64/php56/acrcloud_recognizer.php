<?php
/*
 *   @author qinxue.pan E-mail: xue@acrcloud.com
 *   @version 1.0.0
 *   @create 2016.05.13
 * 
 * Copyright 2016 ACRCloud Recognizer v1.0.0
 * This module can recognize ACRCloud by most of audio/video file. 
 *        Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...
 *        Video: mp4, mkv, wmv, flv, ts, avi ...
 *  
*/
namespace ACRCloud {
    class ACRCloudRecognizer {
        private $host = "";
        private $access_key = "";
        private $access_secret = "";
        private $timeout = 5; // s
        private $recognizer_audio_len = 12; // seconds

        function __construct($config) {
            if (array_key_exists('host', $config)) {
                $this->host = $config['host'];
            } 
            if (array_key_exists('access_key', $config)) {
                $this->access_key = $config['access_key'];
            } 
            if (array_key_exists('access_secret', $config)) {
                $this->access_secret = $config['access_secret'];
            }
            if (array_key_exists('timeout', $config)) {
                $this->timeout = $config['timeout'];
            }
            if (array_key_exists('recognizer_audio_len', $config)) {
                $this->recognizer_audio_len = $config['recognizer_audio_len'];
            }
        }

        /**
          *
          *  recognize by file path of (Audio/Video file)
          *          Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...
          *          Video: mp4, mkv, wmv, flv, ts, avi ...
          *
          *  @param filePath query file path
          *  @param startSeconds skip (startSeconds) seconds from from the beginning of (filePath)
          *  
          *  @return result metainfos https://docs.acrcloud.com/metadata
          *
          **/
        public function recognizeByFile($filePath, $startSeconds) {
	    if(!file_exists($filePath)) {
                return '';
            }

            $fingerprint = ACRCloudExtrTool::createFingerprintByFile($filePath, $startSeconds, $this->recognizer_audio_len, false);
            if ($fingerprint == false) {
                return ACRCloudExceptionCode::getCodeResult(ACRCloudExceptionCode::$GEN_FP_ERROR);
            }
            if ($fingerprint == null) {
                return ACRCloudExceptionCode::getCodeResult(ACRCloudExceptionCode::$NO_RESULT);
            }
            return $this->doRecognize($fingerprint);
        }

       /**
         *
         *  recognize by buffer of (Audio/Video file)
         *          Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...
         *          Video: mp4, mkv, wmv, flv, ts, avi ...
         *
         *  @param fileBuffer query buffer
         *  @param startSeconds skip (startSeconds) seconds from from the beginning of fileBuffer
         *  
         *  @return result metainfos https://docs.acrcloud.com/metadata
         *
         **/
         public function recognizeByFileBuffer($fileBuffer, $startSeconds) {
            $fingerprint = ACRCloudExtrTool::createFingerprintByFileBuffer($fileBuffer, $startSeconds, $this->recognizer_audio_len, false);
            if ($fingerprint == false) {
                return ACRCloudExceptionCode::getCodeResult(ACRCloudExceptionCode::$GEN_FP_ERROR);
            }
            if ($fingerprint == null) {
                return ACRCloudExceptionCode::getCodeResult(ACRCloudExceptionCode::$NO_RESULT);
            }
            return $this->doRecognize($fingerprint);
        }

        /**
          *
          *  recognize by wav audio buffer(RIFF (little-endian) data, WAVE audio, Microsoft PCM, 16 bit, mono 8000 Hz) 
          *
          *  @param wavAudioBuffer query audio buffer
          *  
          *  @return result metainfos https://docs.acrcloud.com/metadata
          *
          **/
        public function recognize($wavAudioBuffer) {
            $fingerprint = ACRCloudExtrTool::createFingerprint($wavAudioBuffer, false);
            if ($fingerprint == null) {
                return ACRCloudExceptionCode::getCodeResult(ACRCloudExceptionCode::$NO_RESULT);
            }
            return $this->doRecognize($fingerprint);
        }

        private function doRecognize($audio_fingerprint) {
            $http_method = "POST";
            $http_uri = "/v1/identify";
            $data_type = "fingerprint";
            $signature_version = "1" ;
            $timestamp = time() ;
            $requrl = $this->host . "/v1/identify";

            $string_to_sign = $http_method . "\n" . $http_uri ."\n" . $this->access_key . "\n" . 
                     $data_type . "\n" . $signature_version . "\n" . $timestamp;

            $result = '';
            try {
                $signature = hash_hmac("sha1", $string_to_sign, $this->access_secret, true);
                $signature = base64_encode($signature);
                $post_arrays = array(
                    "sample" => $audio_fingerprint,
                    "sample_bytes"=>strlen($audio_fingerprint),
                    "access_key"=>$this->access_key, 
                    "data_type"=>$data_type, 
                    "signature"=>$signature, 
                    "signature_version"=>$signature_version, 
                    "timestamp"=>$timestamp
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $requrl);
                curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_arrays);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($ch);
                curl_close($ch);
            } catch (Exception $e) {
                $result = ACRCloudExceptionCode::getCodeResultMsg(ACRCloudExceptionCode::$HTTP_ERROR, $e->getMessage());
            }

            return $result;
        }
    }

    class ACRCloudExceptionCode {
        public static $NO_RESULT = 1000;
        public static $JSON_ERROR = 2002;
        public static $HTTP_ERROR = 3000;
        public static $GEN_FP_ERROR = 2004;
        public static $UNKNOW_ERROR = 2010;

        private static $code_msg_map = array(
            1000 => "No Result",
            2002 => "Json Error",
            3000 => "Http Error",
            2004 => "gen fingerprint error",
            2010 => 'unknow error'
        );

        public static function getCodeResult($code) {
            $tmp = array('status'=>array('msg'=>self::$code_msg_map[$code], 'code'=>$code, 'version'=>'1.0'));
            return json_encode($tmp);
        }

        public static function getCodeResultMsg($code, $msg) {
            $tmp = array('status'=>array('msg'=>$msg, 'code'=>$code, 'version'=>'1.0'));
            return json_encode($tmp);
        }

    }

    class ACRCloudExtrTool {
        /**
          *
          *  create "ACRCloud Fingerprint" by wav audio buffer(RIFF (little-endian) data, WAVE audio, Microsoft PCM, 16 bit, mono 8000 Hz) 
          *
          *  @param pcmBuffer query audio buffer
          *  @param isDB   If it is True, it will create db frigerprint; 
          *  
          *  @return result "ACRCloud Fingerprint"
          *
          **/
        public static function createFingerprint($pcmBuffer, $isDB) {
            return acrcloud_create_fingerprint($pcmBuffer, $isDB);
        }

        /**
          *
          *  create "ACRCloud Fingerprint" by file path of (Audio/Video file)
          *          Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...
          *          Video: mp4, mkv, wmv, flv, ts, avi ...
          *
          *  @param filePath query file path
          *  @param startTimeSeconds skip (startSeconds) seconds from from the beginning of (filePath)
          *  @param audioLenSeconds Length of audio data you need. if you create recogize frigerprint, default is 12 seconds, if you create db frigerprint, it is not usefully; 
          *  @param isDB   If it is True, it will create db frigerprint; 
          *  
          *  @return result "ACRCloud Fingerprint"
          *     null: can not create fingerprint, maybe mute.
          *     false: can decode audio from $filePath.
          *     throw Exception: other error, or params error.
          *
          **/
        public static function createFingerprintByFile($filePath, $startTimeSeconds, $audioLenSeconds, $isDB) {
            return acrcloud_create_fingerprint_by_file($filePath, $startTimeSeconds, $audioLenSeconds, $isDB);
        }

        /**
          *
          *  create "ACRCloud Fingerprint" by file buffer of (Audio/Video file)
          *          Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...
          *          Video: mp4, mkv, wmv, flv, ts, avi ...
          *
          *  @param fileBuffer data buffer of input file
          *  @param fileBufferLen  length of fileBuffer
          *  @param startTimeSeconds skip (startSeconds) seconds from from the beginning of (filePath)
          *  @param audioLenSeconds Length of audio data you need. if you create recogize frigerprint, default is 12 seconds, if you create db frigerprint, it is not usefully; 
          *  @param isDB   If it is True, it will create db frigerprint; 
          *  
          *  @return result "ACRCloud Fingerprint"
          *     null: can not create fingerprint
          *     false: can decode audio from $filePath.
          *     throw Exception: other error, or params error.
          *
          **/
        public static function createFingerprintByFileBuffer($fileBuffer, $startTimeSeconds, $audioLenSeconds, $isDB) {
            return acrcloud_create_fingerprint_by_filebuffer($fileBuffer, $startTimeSeconds, $audioLenSeconds, $isDB);
        }

        /**
          *
          *  decode audio from file path of (Audio/Video file)
          *          Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...
          *          Video: mp4, mkv, wmv, flv, ts, avi ...
          *
          *  @param filePath query file path
          *  @param startTimeSeconds skip (startSeconds) seconds from from the beginning of (filePath)
          *  @param audioLenSeconds Length of audio data you need, if it is 0, will decode all the audio;  
          *  
          *  @return result audio data(formatter:RIFF (little-endian) data, WAVE audio, Microsoft PCM, 16 bit, mono 8000 Hz)
          *      null: can not decode audio from $filePath
          *
          **/
        public static function decodeAudioByFile($filePath, $startTimeSeconds, $audioLenSeconds) {
            return acrcloud_decode_audio_by_file($filePath, $startTimeSeconds, $audioLenSeconds);
        }

        /**
          *
          *  decode audio from file buffer of (Audio/Video file)
          *          Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...
          *          Video: mp4, mkv, wmv, flv, ts, avi ...
          *
          *  @param fileBuffer data buffer of input file
          *  @param startTimeSeconds skip (startSeconds) seconds from from the beginning of (filePath)
          *  @param audioLenSeconds Length of audio data you need, if it is 0, will decode all the audio;  
          *  
          *  @return result audio data(formatter:RIFF (little-endian) data, WAVE audio, Microsoft PCM, 16 bit, mono 8000 Hz)
          *
          **/
        public static function decodeAudioByFileBuffer($fileBuffer, $startTimeSeconds, $audioLenSeconds) {
            return acrcloud_decode_audio_by_filebuffer($fileBuffer, $startTimeSeconds, $audioLenSeconds);
        }

        public static function getDurationFromFile($filePath) {
            return acrcloud_get_duration_ms_by_file($filePath);
        }

        public static function setDebug($isDebug) {
           acrcloud_set_debug_mode($isDebug);
        }
    }
}
?>
