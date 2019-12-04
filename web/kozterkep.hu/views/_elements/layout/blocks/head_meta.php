<?php
$url = CORE['BASE_URL'] . $_params->here;

if (isset($_meta['title']) && $_meta['title'] != '') {
  $title = $_meta['title'];
} else {
  $title = @$_title != '' ? strip_tags($_title) . ' &ndash; ' . APP['title'] : APP['title'];
}

$description = @$_meta['description'] != '' ? $_meta['description'] . ' -- ' . APP['description'] : APP['description'];

$image = @$_meta['image'] != '' ? $_meta['image'] : CORE['BASE_URL'] . '/img/kozterkep-app-icon.jpg';
?><meta name="description" content="<?=$description?>" />
  <meta property="fb:app_id" content="<?=C_WS_FACEBOOK['app_id']?>" />
  <meta property="og:url" content="<?=$url?>" />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="<?=$title?>" />
  <meta property="og:description" content="<?=$description?>" />
  <meta property="og:image" id="metaimage" content="<?=$image?>" />
