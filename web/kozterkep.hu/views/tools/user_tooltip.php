<?php
if ($user) {
  echo $app->element('community/user', [
    'user' => $user,
    'options' => [
      'link' => false,
      'instroduction' => true,
      'class' => '',
    ]
  ]);
}