<?php

use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;

require_once __DIR__ . "/vendor/autoload.php";

const FRAME_STEP = 3;
const ORIGINAL_VIDEO = __DIR__ . "/video/rickroll-gif.gif";
const CONVERTED_VIDEO = __DIR__ . "/video/rickroll-gif-converted.ogg";
const FRAME_WIDTH = 52;
const FRAME_HEIGHT = 32;

$ffmpeg = FFMpeg::create(
    [
        'ffmpeg.binaries' => __DIR__ . '/vendor/bin/ffmpeg',
        'ffprobe.binaries' => __DIR__ . '/vendor/bin/ffprobe',
        'auto-alt-ref' => 0
    ]
);

/**
 * @var \FFMpeg\Media\Video
 */
$video = $ffmpeg->open(ORIGINAL_VIDEO);
$video->addFilter(new \FFMpeg\Filters\Video\CustomFilter("eq=saturation=0"));
$video
    ->filters()
    ->resize(new Dimension(FRAME_WIDTH, FRAME_HEIGHT));

$video->save(new \FFMpeg\Format\Video\Ogg(), CONVERTED_VIDEO);

//----------------

$video = $ffmpeg->open(CONVERTED_VIDEO);
$format = $video->getFFProbe()->format(CONVERTED_VIDEO);

$duration = $format->get('duration');
$frameCount = $duration * 50; //gif framerate is 15 to 30, so...
$currentFrame = 0;
$i = 1;
while($frameCount > $currentFrame) {
    $currentFrame = $currentFrame + FRAME_STEP;
    $frame = $video->frame(new TimeCode(0, 0, 0, $currentFrame));
    $frame->save(__DIR__ . "/frames/frame$i" . ".jpeg");
    $i++;
}
