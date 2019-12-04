<div class="row m-0">
  <?php
  echo $app->Form->create(null, [
    'method' => 'post',
    'class' => 'col-md-5 mb-4',
    'id' => 'Form-Users-Settings-Password',
    'ia-form-change-alert' => 1,
  ]);

  echo $app->Form->input('current_pass', [
    'type' => 'password',
    'label' => 'Jelenlegi jelszó',
  ]);

  echo $app->Form->input('new_pass', [
    'type' => 'password',
    'label' => 'Új jelszó',
    'help' => 'Új jelszavad legalább 5 karakter hosszú legyen.'
  ]);

  echo $app->Form->input('confirm_new_pass', [
    'type' => 'password',
    'label' => 'Új jelszó megismétlése'
  ]);

  echo $app->Form->end('Mentés', [
    'name' => 'jelszo-csere',
    'class' => 'btn-primary'
  ]);

  ?>

  <div class="col-md-7">
    <h4 class="title">Biztonsági értesítés megy</h4>
    <p>Az új jelszó mentését követően biztonsági okból email értesítést küldünk a regisztrált email címedre.</p>
  </div>
</div>