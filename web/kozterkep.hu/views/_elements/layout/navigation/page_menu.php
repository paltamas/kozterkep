<?php
if (@$_followable == true && $_user && $app->Users->can_do('follow', $_params, $_user)) {
  echo $app->Html->link('Követés', '#', [
    'icon' => 'star fa-lg',
    'class' => 'text-muted ml-3 d-none',
    'ia-follow' => 'this',
    'hide_text' => true,
  ]);
}

if (@$_viewable != '') {
  echo $app->Html->link('Megtekintés', $_viewable, [
    'icon' => 'eye fa-lg',
    'class' => 'btn btn-outline-primary ml-3 text-nowrap',
    'hide_text' => true,
  ]);
}

if (@$_shareable !== false) {
  echo $app->Html->link('', '#', [
    'icon' => 'share fa-lg',
    'class' => 'text-muted ml-3 text-nowrap',
    'title' => 'Oldal megosztása',
    'ia-bind' => 'document.share',
    'ia-pass' => $_params->url,
    'ia-vars-title' => $_title,
  ]);
}

if (@$_bookmarkable !== false && $_user && 1 == 2) {
  echo $app->Html->link('', '#', [
    'icon' => 'bookmark fa-lg',
    'class' => 'text-muted ml-3',
    'ia-bind' => 'document.bookmark',
    'ia-pass' => $_params->url,
    'ia-vars-title' => $_title,
  ]);
}

if (@$_printable !== false && 1 == 2) {
  echo $app->Html->link('', '#', [
    'icon' => 'print fa-lg',
    'class' => 'text-muted ml-3',
    'ia-bind' => 'document.print',
    'title' => 'Oldal nyomtatása',
  ]);
}

if (@$_editable != '' && $_user && $app->Users->can_do('edit', $_params, $_user)) {
  echo $app->Html->link('Szerkesztés', $_editable, [
    'icon' => 'edit fa-lg',
    'class' => 'btn btn-outline-primary ml-3 text-nowrap',
    'hide_text' => true,
  ]);
}