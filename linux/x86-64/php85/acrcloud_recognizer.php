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
    class ACRCloudRecognizeType {
        const ACR_OPT_REC_AUDIO = 0;  # audio fingerprint
        const ACR_OPT_REC_HUMMING = 1; # humming fingerprint
        const ACR_OPT_REC_BOTH = 2; # audio and humming fingerprint
    }

    class ACRCloudRecognizer {
        private $host = "";
        private $access_key = "";
        private $access_secret = "";
        private $timeout = 5; // s
        private $recognize_type = ACRCloudRecognizeType::ACR_OPT_REC_AUDIO;

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
            if (array_key_exists('recognize_type', $config)) {
                $this->recognize_type = $config['recognize_type'];
                if ($this->recognize_type > 2) {
                    $recognize_type = ACRCloudRecognizeType::ACR_OPT_REC_AUDIO;
                }
            }
        }

        /**
          *
          *  recognize by file path of (Audio/Video file)
          *          Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...
          *          Video: mp4, mkv, wmv, flv, ts, avi ...
          *
          *  @param file_path query file path
          *  @param start_seconds skip (start_seconds) seconds from from the beginning of (file_path)
          *  
          *  @return result metainfos https://docs.acrcloud.com/metadata
          *
          **/
        public function recognizeByFile($file_path, $start_seconds, $recognizer_audio_len = 10, $user_params = array()) {
	    if(!file_exists($file_path)) {
                return ACRCloudExceptionCode::getCodeResult(ACRCloudExceptionCode::$GEN_FP_ERROR);
            }

            $query_data = array();
            if ($this->recognize_type == ACRCloudRecognizeType::ACR_OPT_REC_AUDIO || $this->recognize_type == ACRCloudRecognizeType::ACR_OPT_REC_BOTH) {
                $query_data['sample'] = ACRCloudExtrTool::createFingerprintByFile($file_path, $start_seconds, $recognizer_audio_len, False);
            }
            if ($this->recognize_type == ACRCloudRecognizeType::ACR_OPT_REC_HUMMING || $this->recognize_type == ACRCloudRecognizeType::ACR_OPT_REC_BOTH) {
                $query_data['sample_hum'] = ACRCloudExtrTool::createHummingFingerprintByFile($file_path, $start_seconds, $recognizer_audio_len);
            }

            return $this->doRecognize($query_data, $user_params);
        }

       /**
         *
         *  recognize by buffer of (Audio/Video file)
         *          Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...
         *          Video: mp4, mkv, wmv, flv, ts, avi ...
         *
         *  @param file_buffer query buffer
         *  @param start_seconds skip (start_seconds) seconds from from the beginning of file_buffer
         *  
         *  @return result metainfos https://docs.acrcloud.com/metadata
         *
         **/
         public function recognizeByFileBuffer($file_buffer, $start_seconds, $recognizer_audio_len = 10, $user_params = array()) {
            $query_data = array();
            if ($this->recognize_type == ACRCloudRecognizeType::ACR_OPT_REC_AUDIO || $this->recognize_type == ACRCloudRecognizeType::ACR_OPT_REC_BOTH) {
                $query_data['sample'] = ACRCloudExtrTool::createFingerprintByFileBuffer($file_buffer, $start_seconds, $recognizer_audio_len, false);
            }
            if ($this->recognize_type == ACRCloudRecognizeType::ACR_OPT_REC_HUMMING || $this->recognize_type == ACRCloudRecognizeType::ACR_OPT_REC_BOTH) {
                $query_data['sample_hum'] = ACRCloudExtrTool::createHummingFingerprintByFileBuffer($file_buffer, $start_seconds, $recognizer_audio_len);
            }

            return $this->doRecognize($query_data, $user_params);
        }

       /**
         *
         *  recognize by DB fingerprint buffer
         *
         *  @param fp_buffer query buffer
         *  @param start_seconds skip (start_seconds) seconds from from the beginning of file_buffer
         *  
         *  @return result metainfos https://docs.acrcloud.com/metadata
         *
         **/
         public function recognizeByFpBuffer($fp_buffer, $start_seconds, $recognizer_audio_len = 10, $user_params = array()) {
            $query_data = array();
            if ($this->recognize_type == ACRCloudRecognizeType::ACR_OPT_REC_AUDIO || $this->recognize_type == ACRCloudRecognizeType::ACR_OPT_REC_BOTH) {
                $query_data['sample'] = ACRCloudExtrTool::createFingerprintByFpBuffer($fp_buffer, $start_seconds, $recognizer_audio_len);
            }

            return $this->doRecognize($query_data, $user_params);
        }


        /**
          *
          *  recognize by wav audio buffer(RIFF (little-endian) data, WAVE audio, Microsoft PCM, 16 bit, mono 8000 Hz) 
          *
          *  @param pcm_audio_buffer query audio buffer
          *  
          *  @return result metainfos https://docs.acrcloud.com/metadata
          *
          **/
        public function recognize($pcm_audio_buffer, $user_params = array()) {
            $query_data = array();
            if ($this->recognize_type == ACRCloudRecognizeType::ACR_OPT_REC_AUDIO || $this->recognize_type == ACRCloudRecognizeType::ACR_OPT_REC_BOTH) {
                $query_data['sample'] = ACRCloudExtrTool::createFingerprint($pcm_audio_buffer, false);
            }
            if ($this->recognize_type == ACRCloudRecognizeType::ACR_OPT_REC_HUMMING || $this->recognize_type == ACRCloudRecognizeType::ACR_OPT_REC_BOTH) {
                $query_data['sample_hum'] = ACRCloudExtrTool::createHummingFingerprint($pcm_audio_buffer);
            }

            return $this->doRecognize($query_data, $user_params);
        }

        private function doRecognize($query_data, $user_params) {
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
                    "access_key"=>$this->access_key, 
                    "data_type"=>$data_type, 
                    "signature"=>$signature, 
                    "signature_version"=>$signature_version, 
                    "timestamp"=>$timestamp
                );

                if ($user_params) {
                    foreach ($user_params as $ukey => $uvalue) {
                        $post_arrays[$ukey] = $uvalue;
                    }
                }

                $sample_bytes = 0;
                $sample_hum_bytes = 0;
                if (array_key_exists('sample', $query_data)) {
                    if ($query_data["sample"] == false) {
                        return ACRCloudExceptionCode::getCodeResult(ACRCloudExceptionCode::$GEN_FP_ERROR);
                    }
                    $post_arrays["sample"] = $query_data["sample"];
                    $sample_bytes = strlen($query_data["sample"]);
                    $post_arrays["sample_bytes"] = $sample_bytes;
                }
                if (array_key_exists('sample_hum', $query_data)) {
                    if ($query_data["sample_hum"] == false) {
                        return ACRCloudExceptionCode::getCodeResult(ACRCloudExceptionCode::$GEN_FP_ERROR);
                    }
                    $post_arrays["sample_hum"] = $query_data["sample_hum"];
                    $sample_hum_bytes = strlen($query_data["sample_hum"]);
                    $post_arrays["sample_hum_bytes"] = $sample_bytes;
                }
                if ($sample_bytes == 0 && $sample_hum_bytes == 0) {
                    return ACRCloudExceptionCode::getCodeResult(ACRCloudExceptionCode::$NO_RESULT);
                }

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $requrl);
                curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_arrays);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($ch);
                $errno = curl_errno($ch);
                curl_close($ch);

                if ($errno == 28) {
                    return ACRCloudExceptionCode::getCodeResultMsg(ACRCloudExceptionCode::$HTTP_ERROR, "HTTP TIMEOUT");
                } else if ($errno) {
                    return ACRCloudExceptionCode::getCodeResultMsg(ACRCloudExceptionCode::$UNKNOW_ERROR, "errno:".$errno);
                }

                try {
                    if (!json_decode($result)) {
                        return ACRCloudExceptionCode::getCodeResultMsg(ACRCloudExceptionCode::$JSON_ERROR, $result);
                    }
                } catch (Exception $e) {
                    return ACRCloudExceptionCode::getCodeResult(ACRCloudExceptionCode::$JSON_ERROR);
                }
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
            $tmp = array('status'=>array('msg'=>self::$code_msg_map[$code].":".$msg, 'code'=>$code, 'version'=>'1.0'));
            return json_encode($tmp);
        }

    }

    class ACRCloudExtrTool {
        /**
          *
          *  create "ACRCloud Fingerprint" by wav audio buffer(RIFF (little-endian) data, WAVE audio, Microsoft PCM, 16 bit, mono 8000 Hz) 
          *
          *  @param pcm_buffer query audio buffer
          *  @param is_db   If it is True, it will create db frigerprint; 
          *  
          *  @return result "ACRCloud Fingerprint"
          *
          **/
        public static function createFingerprint($pcm_buffer, $is_db) {
            return acrcloud_create_fingerprint($pcm_buffer, $is_db);
        }

        /**
          *
          *  create "ACRCloud Humming Fingerprint" by wav audio buffer(RIFF (little-endian) data, WAVE audio, Microsoft PCM, 16 bit, mono 8000 Hz) 
          *
          *  @param pcm_buffer query audio buffer
          *  @param is_db   If it is True, it will create db frigerprint; 
          *  
          *  @return result "ACRCloud Humming Fingerprint"
          *
          **/
        public static function createHummingFingerprint($pcm_buffer) {
            return acrcloud_create_humming_fingerprint($pcm_buffer);
        }

        /**
          *
          *  create "ACRCloud Fingerprint" by file path of (Audio/Video file)
          *          Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...
          *          Video: mp4, mkv, wmv, flv, ts, avi ...
          *
          *  @param file_path query file path
          *  @param start_seconds skip (start_seconds) seconds from from the beginning of (file_path)
          *  @param audio_len_seconds Length of audio data you need. if you create recogize frigerprint, default is 12 seconds, if you create db frigerprint, it is not usefully; 
          *  @param is_db   If it is True, it will create db frigerprint; 
          *  
          *  @return result "ACRCloud Fingerprint"
          *     null: can not create fingerprint, maybe mute.
          *     false: can decode audio from $file_path.
          *     throw Exception: other error, or params error.
          *
          **/
        public static function createFingerprintByFile($file_path, $start_seconds, $audio_len_seconds, $is_db) {
            return acrcloud_create_fingerprint_by_file($file_path, $start_seconds, $audio_len_seconds, $is_db);
        }

        /**
          *
          *  create "ACRCloud Humming Fingerprint" by file path of (Audio/Video file)
          *          Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...
          *          Video: mp4, mkv, wmv, flv, ts, avi ...
          *
          *  @param file_path query file path
          *  @param start_seconds skip (start_seconds) seconds from from the beginning of (file_path)
          *  @param audio_len_seconds Length of audio data you need. if you create recogize frigerprint, default is 12 seconds, if you create db frigerprint, it is not usefully; 
          *  
          *  @return result "ACRCloud Humming Fingerprint"
          *     null: can not create fingerprint, maybe mute.
          *     false: can decode audio from $file_path.
          *     throw Exception: other error, or params error.
          *
          **/
        public static function createHummingFingerprintByFile($file_path, $start_seconds, $audio_len_seconds) {
            return acrcloud_create_humming_fingerprint_by_file($file_path, $start_seconds, $audio_len_seconds);
        }

        /**
          *
          *  create "ACRCloud Fingerprint" by file buffer of (Audio/Video file)
          *          Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...
          *          Video: mp4, mkv, wmv, flv, ts, avi ...
          *
          *  @param file_buffer data buffer of input file
          *  @param file_bufferLen  length of file_buffer
          *  @param start_seconds skip (start_seconds) seconds from from the beginning of (file_path)
          *  @param audio_len_seconds Length of audio data you need. if you create recogize frigerprint, default is 12 seconds, if you create db frigerprint, it is not usefully; 
          *  @param is_db   If it is True, it will create db frigerprint; 
          *  
          *  @return result "ACRCloud Fingerprint"
          *     null: can not create fingerprint
          *     false: can decode audio from $file_path.
          *     throw Exception: other error, or params error.
          *
          **/
        public static function createFingerprintByFileBuffer($file_buffer, $start_seconds, $audio_len_seconds, $is_db) {
            return acrcloud_create_fingerprint_by_filebuffer($file_buffer, $start_seconds, $audio_len_seconds, $is_db);
        }

        public static function createFingerprintByFpBuffer($fp_buffer, $start_seconds, $audio_len_seconds) {
            return acrcloud_create_fingerprint_by_fpbuffer($fp_buffer, $start_seconds, $audio_len_seconds);
        }

        /**
          *
          *  create "ACRCloud Humming Fingerprint" by file buffer of (Audio/Video file)
          *          Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...
          *          Video: mp4, mkv, wmv, flv, ts, avi ...
          *
          *  @param file_buffer data buffer of input file
          *  @param file_bufferLen  length of file_buffer
          *  @param start_seconds skip (start_seconds) seconds from from the beginning of (file_path)
          *  @param audio_len_seconds Length of audio data you need. if you create recogize frigerprint, default is 12 seconds, if you create db frigerprint, it is not usefully; 
          *  @param is_db   If it is True, it will create db frigerprint; 
          *  
          *  @return result "ACRCloud Humming Fingerprint"
          *     null: can not create fingerprint
          *     false: can decode audio from $file_path.
          *     throw Exception: other error, or params error.
          *
          **/
        public static function createHummingFingerprintByFileBuffer($file_buffer, $start_seconds, $audio_len_seconds) {
            return acrcloud_create_humming_fingerprint_by_filebuffer($file_buffer, $start_seconds, $audio_len_seconds);
        }

        /**
          *
          *  decode audio from file path of (Audio/Video file)
          *          Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...
          *          Video: mp4, mkv, wmv, flv, ts, avi ...
          *
          *  @param file_path query file path
          *  @param start_seconds skip (start_seconds) seconds from from the beginning of (file_path)
          *  @param audio_len_seconds Length of audio data you need, if it is 0, will decode all the audio;  
          *  
          *  @return result audio data(formatter:RIFF (little-endian) data, WAVE audio, Microsoft PCM, 16 bit, mono 8000 Hz)
          *      null: can not decode audio from $file_path
          *
          **/
        public static function decodeAudioByFile($file_path, $start_seconds, $audio_len_seconds) {
            return acrcloud_decode_audio_by_file($file_path, $start_seconds, $audio_len_seconds);
        }

        /**
          *
          *  decode audio from file buffer of (Audio/Video file)
          *          Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...
          *          Video: mp4, mkv, wmv, flv, ts, avi ...
          *
          *  @param file_buffer data buffer of input file
          *  @param start_seconds skip (start_seconds) seconds from from the beginning of (file_path)
          *  @param audio_len_seconds Length of audio data you need, if it is 0, will decode all the audio;  
          *  
          *  @return result audio data(formatter:RIFF (little-endian) data, WAVE audio, Microsoft PCM, 16 bit, mono 8000 Hz)
          *
          **/
        public static function decodeAudioByFileBuffer($file_buffer, $start_seconds, $audio_len_seconds) {
            return acrcloud_decode_audio_by_filebuffer($file_buffer, $start_seconds, $audio_len_seconds);
        }

        public static function getDurationFromFile($file_path) {
            return acrcloud_get_duration_ms_by_file($file_path);
        }

        public static function getDurationFromFpBuffer($fp_buffer) {
            return acrcloud_get_duration_ms_by_fpbuffer($fp_buffer);
        }

        public static function setDebug($is_debug) {
           acrcloud_set_debug_mode($is_debug);
        }
    }
}
?>
