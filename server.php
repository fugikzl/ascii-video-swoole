<?php

\Swoole\Runtime::enableCoroutine();

const ASCII_FRAME_DIR = __DIR__ . "/frames-ascii";

$frames = [];
$dir = new DirectoryIterator(ASCII_FRAME_DIR);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
        $name = $fileinfo->getFilename();
        $frame = (int)str_replace("frame", "", explode(".jpeg", $name)[0]);
        if($frame !== 0) {
            $frames[] = $frame;
        }
    }
}
sort($frames);
$framesMap = [];
foreach($frames as $frame) {
    $framesMap[] = file_get_contents(ASCII_FRAME_DIR . "/$frame");
}
$server = new \Swoole\Http\Server('0.0.0.0', '6666');
$server->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) use ($framesMap) {
    foreach($framesMap as $frame) {
        $response->write("\033[H");
        $response->write("\n$frame\n");
        usleep(30000);
    }
    $response->end();
});


$server->start();
