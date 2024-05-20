<?php

use function Co\go;

Swoole\Runtime::enableCoroutine(); //i have swoole installed, so why not to use???

const FRAME_WIDTH = 52;
const FRAME_HEIGHT = 32;
const FRAME_DIR = __DIR__ . "/frames";
const ASCII_FRAME_DIR = __DIR__ . "/frames-ascii";
const MAX_BRIGHTNESS = 255 + 255 + 255;
const SCALE = '$@B%8&WM#*oahkbdpqwmZO0QLCJUYXzcvunxrjft/\|()1{}[]?-_+~<>i!lI;:,"^`\'.';

$scaleArray = str_split(SCALE);
$scaleLength = count($scaleArray);

function getFrameColor(GdImage $image, int $x, int $y): int
{
    $rgb = imagecolorat($image, $x, $y);
    $red = ($rgb >> 16) & 255;
    $green = ($rgb >> 8) & 255;
    $blue = $rgb & 255;

    return $red + $green + $blue;
}

function getFrameChar(GdImage $image, int $x, int $y): string
{
    global $scaleLength;
    $color = getFrameColor($image, $x, $y);
    $index = intval((MAX_BRIGHTNESS / ($scaleLength)) * ($color / MAX_BRIGHTNESS));
    return SCALE[$scaleLength - $index - 1];
}

function getFrameMap(int $frameNum): string
{
    $framePath = FRAME_DIR . "/frame$frameNum.jpeg";
    $image = imagecreatefromjpeg($framePath);
    $res = "";

    for($y = 0; $y < FRAME_HEIGHT; $y++) {
        for($x = 0; $x < FRAME_WIDTH; $x++) {
            $res .= getFrameChar($image, $x, $y);
        }
        $res .= "\n";
    }

    return $res;
}


$dir = new DirectoryIterator(FRAME_DIR);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
        $name = $fileinfo->getFilename();
        $frame = (int)str_replace("frame", "", explode(".jpeg", $name)[0]);
        if($frame !== 0) {
            go(function () use ($frame) {
                file_put_contents(ASCII_FRAME_DIR . "/$frame", getFrameMap($frame));
            });
        }
    }
}
