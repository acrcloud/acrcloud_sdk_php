<?php namespace ACRCloud;
    include_once('acrcloud_recognizer.php');

    // Replace "xxxxxxxx" below with your project's host, access_key and access_secret.
    $config = array(
        'host' => 'XXX',
        'access_key' => 'XXX',
        'access_secret' => 'XXX'
    );
    $re = new ACRCloudRecognizer($config);
    print $re->recognizeByFile($argv[1], 0);

    $content = file_get_contents($argv[1]);
    print $re->recognizeByFileBuffer($content, 0);
?>
