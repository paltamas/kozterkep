<?php
if (@$container) {
  echo '<div class="">';
}

/**
 * Ezek az ikonok csak kisebb kijelzőkön jelennek meg, így
 * itt nem akarom, hogy a nem belépetteknek "zavaró" ikonok jelenjenek meg.
 */
if ($_user) {
  echo '<div class="float-right d-md-none ' , @$container ? '' : 'pt-3' , '">';
  if (@$_bookmarkable != false && 1 == 2) { // mobilon ez most nincs és sztem nem is lesz, ha lefejlesztem
    echo $app->Html->link('', '#', [
      'icon' => 'bookmark fa-lg',
      'class' => 'text-muted d-inline-block d-none',
      'ia-bind' => 'document.bookmark',
      'title' => 'Oldal könyvjelzőzése',
    ]);
  }

  if (@$_followable == true && $_user && $app->Users->can_do('follow', $_params, $_user)) {
    echo $app->Html->link('', '#', [
      'icon' => 'star fa-lg',
      'class' => 'text-muted ml-3 d-inline-block d-none',
      'ia-follow' => 'this',
    ]);
  }

  if (@$_editable != '' && $_user && $app->Users->can_do('edit', $_params, $_user)) {
    echo $app->Html->link('', $_editable, [
      'icon' => 'edit fa-lg',
      'class' => 'text-primary ml-3 d-inline-block d-none',
    ]);
  }
  echo '</div>';
}


// Controllerben definiált tabok
if ($_tabs) {
  echo '<div class="float-left float-md-right pt-2">';
  echo $app->Html->tabs($_tabs['list'], $_tabs['options']);
  echo '</div>';
}

if (@$container) {
  echo '</div>';
  echo '<div class="clearfix"></div>';
}
