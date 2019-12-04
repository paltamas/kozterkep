<div class="mt-4">
  <strong>Legaktívabb felhasználóink toplistája</strong> az elmúlt 12 hónapot alapul véve, a másokhoz tett szerkesztésekre és sztorikra koncentrálva, valamint összesítve.

  <?php
  echo $app->Html->tabs([
    '12 havi' => [
      'hash' => 'mostanaban',
      'icon' => 'calendar-alt',
    ],
    'Szerk.' => [
      'hash' => 'szerkesztesek',
      'icon' => 'edit',
    ],
    'Össz.' => [
      'hash' => 'osszesitett',
      'icon' => 'sigma',
    ],
  ], [
    'type' => 'tabs',
    'align' => 'left',
    'selected' => 1,
    'class' => 'mt-2 pl-3',
  ]);
  ?>

  <div class="tab-content">

    <div class="tab-pane show active" id="mostanaban" role="tabpanel" aria-labelledby="mostanaban-tab">
      <?=$app->element('community/index/top_users_table', [
        'top_users' => $top_users_latest,
        'options' => [
          'latest' => true
        ]
      ])?>
    </div>

    <div class="tab-pane" id="szerkesztesek" role="tabpanel" aria-labelledby="szerkesztesek-tab">
      <?=$app->element('community/index/top_users_edits_table', ['top_users' => $top_users_edits])?>
    </div>

    <div class="tab-pane" id="osszesitett" role="tabpanel" aria-labelledby="osszesitett-tab">
      <?=$app->element('community/index/top_users_table', ['top_users' => $top_users])?>
    </div>

  </div>

  <?=$app->Html->link('További statisztikák', '/kozosseg/statisztikak', [
    'icon_right' => 'arrow-right'
  ])?>

</div>