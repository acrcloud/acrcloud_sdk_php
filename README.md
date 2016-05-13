# Audio Recognition PHP SDK

## Overview
  [ACRCloud](https://www.acrcloud.com/) provides cloud [Automatic Content Recognition](https://www.acrcloud.com/docs/introduction/automatic-content-recognition/) services for [Audio Fingerprinting](https://www.acrcloud.com/docs/introduction/audio-fingerprinting/) based applications such as **[Audio Recognition](https://www.acrcloud.com/music-recognition)** (supports music, video, ads for both online and offline), **[Broadcast Monitoring](https://www.acrcloud.com/broadcast-monitoring)**, **[Second Screen](https://www.acrcloud.com/second-screen-synchronization)**, **[Copyright Protection](https://www.acrcloud.com/copyright-protection-de-duplication)** and etc.<br>
  
  This **audio recognition PHP SDK** support most of audio / video files. 

>>Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...<br>
>>Video: mp4, mkv, wmv, flv, ts, avi ...

## Requirements
Follow the tutorials to create a project and get your host, access_key and access_secret.

 * [How to identify songs by sound](https://www.acrcloud.com/docs/tutorials/identify-music-by-sound/)
 
 * [How to detect custom audio content by sound](https://www.acrcloud.com/docs/tutorials/identify-audio-custom-content/)
 
## Windows Runtime Library 
**If you run the SDK on Windows, you must install this library.**<br>
X86: [download and install Library(windows/vcredist_x86.exe)](https://www.microsoft.com/en-us/download/details.aspx?id=5555)<br>
x64: [download and install Library(windows/vcredist_x64.exe)](https://www.microsoft.com/en-us/download/details.aspx?id=14632)

## Note
1. If you run the SDK on Windows, you must install library(vcredist).

## Install modules
You may be modify php.ini
extension_dir = "/usr/lib64/php/modules/" #you can replace your path, and copy acrcloud_extr_tool.so to "extension_dir".
extension=acrcloud_extr_tool.so

## Functions
Introduction all API.
### test.php
```php
    class ACRCloudRecognizer {
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
        public function recognizeByFile($filePath, $startSeconds);

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
         public function recognizeByFileBuffer($fileBuffer, $startSeconds);

        /**
          *
          *  recognize by wav audio buffer(RIFF (little-endian) data, WAVE audio, Microsoft PCM, 16 bit, mono 8000 Hz) 
          *
          *  @param wavAudioBuffer query audio buffer
          *  
          *  @return result metainfos https://docs.acrcloud.com/metadata
          *
          **/
        public function recognize($wavAudioBuffer);
    }
```

## Example
run Test: php test.php test.mp3
```php
<?php namespace ACRCloud;
    include_once('acrcloud_recognizer.php');

    // Replace "xxxxxxxx" below with your project's host, access_key and access_secret.
    $config = array(
        'host' => 'XXX',
        'access_key' => 'XXX',
        'access_secret' => 'XXX'
    );

    // recognize by file path, and skip 0 seconds from from the beginning of sys.argv[1].
    $re = new ACRCloudRecognizer($config);
    print $re->recognizeByFile($argv[1], 0);

    //recognize by file_audio_buffer that read from file path, and skip 0 seconds from from the beginning of sys.argv[1].
    $content = file_get_contents($argv[1]);
    print $re->recognizeByFileBuffer($content, 0);
?>
```
