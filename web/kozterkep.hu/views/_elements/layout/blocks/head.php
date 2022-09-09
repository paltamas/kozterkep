<!DOCTYPE html>
<html lang="hu">
  <head>
    <meta charset="utf-8">
    <title><?= @$_title != '' ? strip_tags($_title) . ' &ndash; ' . APP['title'] : APP['title'] ?></title>
    <meta name="description" content="">
    <meta name="author" content="Pál Tamás">
    <meta name="revised" content="<?=date('D, d M Y H:i:s', @$_generated > 0 ? $_generated : time())?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <?php if ($app->ts('settings_desktop_everything') != 1) { ?>
    <meta name="viewport" id="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no, user-scalable=no, viewport-fit=cover">
    <?php } ?>
    <meta name="theme-color" content="#EFEDE5">
    <?=$app->element('layout/blocks/css_loader')?>
    <?=$app->element('layout/blocks/head_meta')?>
    <link rel="icon" sizes="192x192" type="image/png" href="/img/kozterkep-app-icon.png" />
    <link rel="apple-touch-icon" href="/img/kozterkep-app-icon.png">
    <meta name="msapplication-square310x310logo" content="/img/kozterkep-app-icon.png">
    <meta name="apple-itunes-app" content="app-id=id1150066881"/>
    <meta name="google-play-app" content="hu.idealap.kt2"/>
    <link rel="manifest" href="/manifest.json">
    <link rel="alternate" type="application/rss+xml" title="Köztérkép műlapok" href="/feed.rss" />
    <link rel="sitemap" href="/sitemap.xml" />
  </head>
  <body>
  <div class="progress rounded-0 fixed-top d-none">
    <div class="progress-bar progress-bar-striped progress-bar-animated bg-green-kt" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
  </div>
