<?php namespace ACRCloud;
    include_once('acrcloud_recognizer.php');

    // Replace "xxxxxxxx" below with your project's host, access_key and access_secret.
    $config = array(
        'host' => 'XXX',
        'access_key' => 'XXX',
        'access_secret' => 'XXX',
        'recognize_type' => ACRCloudRecognizeType::ACR_OPT_REC_AUDIO // ACR_OPT_REC_AUDIO/ACR_OPT_REC_HUMMING/ACR_OPT_REC_BOTH
    );
    $re = new ACRCloudRecognizer($config);
    print $re->recognizeByFile($argv[1], 0, 10);

    $content = file_get_contents($argv[1]);
    print $re->recognizeByFileBuffer($content, 0, 10);
?>
