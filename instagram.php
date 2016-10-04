<?php
set_time_limit(0);
date_default_timezone_set('UTC');
require __DIR__.'/vendor/autoload.php';

use Instagram\Instagram;

$instagram = new Instagram('client_id', 'client_secret', 'callback_uri');
$instagram->setAccessToken('Access Token');
$instagram->sign();
$content = $instagram->generic('tags/love/media/recent');

foreach($content->data as $media) {
  echo($media->caption->text . "\n");
}