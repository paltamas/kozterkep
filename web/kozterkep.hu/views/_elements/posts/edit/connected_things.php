<?php
echo '<h5 class="subtitle">Kiemelten kapcsolódó dolgok</h5>';
echo '<p class="text-muted">Amiről a blogbejegyzésed kifejezetten szól. Ezek a bejegyzés jobb hasábjában jelennek meg.</p>';

echo $app->Form->input('artpiece_id', [
  'type' => 'hidden',
  'id' => 'artpiece_id_detailed',
]);
echo $app->Form->input('artpiece_title', [
  'class' => 'noEnterInput',
  'label' => 'Kiemelten kapcsolódó műlap',
  'value' => $post['artpiece_id'] > 0 ? $this->MC->t('artpieces', $post['artpiece_id'])['title'] . ' (AZ: ' . $post['artpiece_id'] . ')' : '',
  'placeholder' => 'Műlap cím, AZ',
  'help' => 'Ha kifejezetten egy alkotásról szól a bejegyzésed, linkeld. Ennél a műlapnál meg fog jelenni a bejegyzésed.',
  'id' => 'artpiece_detailed',
  'ia-auto' => 'artpieces',
  'ia-auto-query' => 'title',
  'ia-auto-key' => 'id',
  'ia-auto-target' => '#artpiece_id_detailed',
]);


echo $app->Form->input('artist_id', [
  'type' => 'hidden',
  'id' => 'artist_id_detailed',
]);
echo $app->Form->input('artist_name', [
  'class' => 'noEnterInput',
  'label' => 'Kapcsolódó alkotó',
  'value' => $post['artist_id'] > 0 ? $this->MC->t('artists', $post['artist_id'])['name'] : '',
  'placeholder' => 'Alkotó név, AZ',
  'help' => 'Ha kifejezetten egy alkotóról szól a bejegyzésed, linkeld. Ennél az alkotónál meg fog jelenni a bejegyzésed.',
  'id' => 'artist_detailed',
  'ia-auto' => 'artists',
  'ia-auto-query' => 'name',
  'ia-auto-key' => 'id',
  'ia-auto-target' => '#artist_id_detailed',
]);


echo $app->Form->input('place_id', [
  'type' => 'hidden',
  'id' => 'place_id_detailed',
]);
echo $app->Form->input('place_name', [
  'class' => 'noEnterInput',
  'label' => 'Kapcsolódó hely',
  'value' => $post['place_id'] > 0 ? $this->MC->t('places', $post['place_id'])['name'] : '',
  'placeholder' => 'Település név, AZ',
  'help' => 'Ha kifejezetten egy helyről szól a bejegyzésed, linkeld. Ennél a településnél meg fog jelenni a bejegyzésed.',
  'id' => 'place_detailed',
  'ia-auto' => 'places',
  'ia-auto-query' => 'name',
  'ia-auto-key' => 'id',
  'ia-auto-target' => '#place_id_detailed',
]);


echo $app->Form->input('folder_id', [
  'label' => 'Kapcsolódó mappa',
  'empty' => '-',
  'options' => $folders,
  'class' => '',
  'help' => 'Válassz publikus mappát, ha annak fájljai kifejezetten a bejegyzéshez került feltöltésre.',
]);

echo $app->Form->input('set_id', [
  'label' => 'Kapcsolódó gyűjtemény',
  'empty' => '-',
  'options' => $sets,
  'class' => '',
  'help' => 'Válassz saját gyűjteményt, ha az kifejezetten a bejegyzéshez passzol.',
]);