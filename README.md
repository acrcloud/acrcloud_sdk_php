# Audio Recognition PHP SDK (php version 5.X)

## Overview
  [ACRCloud](https://www.acrcloud.com/) provides services such as **[Music Recognition](https://www.acrcloud.com/music-recognition)**, **[Broadcast Monitoring](https://www.acrcloud.com/broadcast-monitoring/)**, **[Custom Audio Recognition](https://www.acrcloud.com/second-screen-synchronization%e2%80%8b/)**, **[Copyright Compliance & Data Deduplication](https://www.acrcloud.com/copyright-compliance-data-deduplication/)**, **[Live Channel Detection](https://www.acrcloud.com/live-channel-detection/)**, and **[Offline Recognition](https://www.acrcloud.com/offline-recognition/)** etc.<br>
  
  This **audio recognition PHP SDK** support most of audio / video files. 

>>Audio: mp3, wav, m4a, flac, aac, amr, ape, ogg ...<br>
>>Video: mp4, mkv, wmv, flv, ts, avi ...

## Requirements
Follow one of the tutorials to create a project and get your host, access_key and access_secret.

 * [Recognize Music](https://docs.acrcloud.com/tutorials/recognize-music)
 * [Recognize Custom Content](https://docs.acrcloud.com/tutorials/recognize-custom-content)
 * [Broadcast Monitoring for Music](https://docs.acrcloud.com/tutorials/broadcast-monitoring-for-music)
 * [Broadcast Monitoring for Custom Content](https://docs.acrcloud.com/tutorials/broadcast-monitoring-for-custom-content)
 * [Detect Live & Timeshift TV Channels](https://docs.acrcloud.com/tutorials/detect-live-and-timeshift-tv-channels)
 * [Recognize Custom Content Offline](https://docs.acrcloud.com/tutorials/recognize-custom-content-offline)
 * [Recognize Live Channels and Custom Content](https://docs.acrcloud.com/tutorials/recognize-tv-channels-and-custom-content)

## Windows Runtime Library 
**If you run the SDK on Windows, you must install this library.**<br>
X86: [download and install Library(windows/vcredist_x86.exe)](https://www.microsoft.com/en-us/download/details.aspx?id=5555)<br>
x64: [download and install Library(windows/vcredist_x64.exe)](https://www.microsoft.com/en-us/download/details.aspx?id=14632)

## Note
1. If you run the SDK on Windows, you must install library(vcredist).

## Install modules
**Note: If you use nginx/apache, you can add "phpinfo()" in your code, and find extension dir and the path of "php.ini" from the result info** </br>
1. Find your extension dir, run(this is default extension dir):   </br>
```sh
$ php -ini | grep "extension_dir"
extension_dir => /usr/lib64/php/modules => /usr/lib64/php/modules
```
2. Put "acrcloud_extr_tool.so" to /usr/lib64/php/modules;(Your extension dir) </br>
3. Find your path of php.ini file:  
```sh
$ php -ini | grep "php.ini" 
Loaded Configuration File => /etc/php.ini
If you use this SDK in Web Server, you need to find "php.ini" by "phpinfo()".
```
![image](https://github.com/acrcloud/acrcloud_sdk_php/blob/master/tutorial_images/php.ini.png) <br>
4. Modify file "/etc/php.ini"(Your php.ini) </br>
> extension=acrcloud_extr_tool.so </br>

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
          *  Notice: this function read max 12 seconds from "startSeconds of input file" and only recognize once.
          *
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

         *  Notice: this function read max 12 seconds from "startSeconds of input file" and only recognize once.
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
    // Notice: this function read max 12 seconds from "startSeconds of input file" and only recognize once.
    $re = new ACRCloudRecognizer($config);
    print $re->recognizeByFile($argv[1], 0);

    // recognize by file_audio_buffer that read from file path, and skip 0 seconds from from the beginning of sys.argv[1].
    // Notice: this function read max 12 seconds from "startSeconds of input file" and only recognize once.
    $content = file_get_contents($argv[1]);
    print $re->recognizeByFileBuffer($content, 0);

    // If need scan a audio file, you can refer to this code.
    $file_duration_ms = ACRCloudExtrTool::getDurationFromFile($argv[1]);
    for ($startSeconds=0; $startSeconds<$file_duration_ms/1000; $startSeconds=$startSeconds+12) {
        print $re->recognizeByFile($argv[1], $startSeconds);
    }
?>
```
