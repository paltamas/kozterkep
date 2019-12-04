<?php
$options = (array)@$options + [
  'class' => 'img-fluid',
  'folder' => false,
  'folder_details' => false,
];

$path = $app->File->get_file_path($file);

$s = '';

$download_link = '<div class="small mt-2">';
$download_link .= $app->Html->link('Fájl letöltése',
  '/mappak/fajl_mutato/' . $file['id'],
  [
    'icon' => 'download',
    'target' => '_blank',
  ]
);
$download_link .= '</div>';

switch (true) {
  case strpos($file['type'], 'image') !== false:
    if ($file['copied'] == 0) {
      $s .= '<span class="image-badge bg-warning badge" title="Feldolgozás alatt. Néhány percen belül kezeljük az állományt!" data-toggle="tooltip"><span class="far fa-sync-alt fa-fw"></span></span>';
    }
    $s .= $app->Html->image($path, ['class' => $options['class']]);
    break;

  case strpos($file['type'], 'pdf') !== false:
    $s .= '<span class="far fa-file-pdf fa-5x fa-border text-muted my-2"></span>';
    $s .= $download_link;
    break;
  case strpos($file['type'], 'text/plain') !== false:
    $s .= '<span class="far fa-file-alt fa-5x fa-border text-muted my-2"></span>';
    $s .= $download_link;
    break;
  case strpos($file['type'], 'excel') !== false:
    $s .= '<span class="far fa-file-excel fa-5x fa-border text-muted my-2"></span>';
    $s .= $download_link;
    break;
  case strpos($file['type'], 'word') !== false:
    $s .= '<span class="far fa-file-word fa-5x fa-border text-muted my-2"></span>';
    $s .= $download_link;
    break;
  case strpos($file['type'], 'audio') !== false:
    $s .= '<span class="far fa-file-audio fa-5x fa-border text-muted my-2"></span>';
    $s .= $download_link;
    break;
  case strpos($file['type'], 'video') !== false:
    $s .= '<span class="far fa-file-video fa-5x fa-border text-muted my-2"></span>';
    $s .= $download_link;
    break;
  default:
    $s .= '<span class="far fa-file fa-5x fa-border text-muted my-2"></span>';
    $s .= $download_link;
    break;

}

if (@$link) {
  $s = $app->Html->link(
    $s,
    $link == 'self' ? $path : $link,
    @$link_options
  );
}

if ($options['folder_details'] && @$file['folder_id'] > 0) {
  $folder = $options['folder'] ? $options['folder'] : $app->MC->t('folders', $file['folder_id']);
  $user = $app->MC->t('users', $file['user_id']);
  if ($folder && $user) {
    $s .= '<div class="mt-1 text-center small">';
    $s .= $app->Html->link($folder['name'], '', [
      'folder' => $folder,
      'class' => 'font-weight-bold',
    ]);
    $s .= '</div>';
    $s .= '<div class="mt-1 text-left small">';
    $s .= $app->Users->name($user, [
      'image' => true,
      'tooltip' => true,
    ]);
    $s .= '<span class="float-right text-muted ml-3">' . _time($file['created'], ['ago' => true]) . '</span>';
    $s .= '</div>';
  }
}

echo '<div class="text-center">';
echo $s;
echo '</div>';