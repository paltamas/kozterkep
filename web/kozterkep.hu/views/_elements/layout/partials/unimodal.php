<?php
$options = (array)@$options + [
  'id' => '',
  'title' => '',
  'body' => '',
  'size' => 'lg',
  'footer_bar' => false,
];
?>
<div class="modal fade uniModal" tabindex="-1" role="dialog" id="<?=$options['id']?>">
  <div class="modal-dialog modal-dialog-centered modal-<?=$options['size']?>" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?=$options['title']?></h5>
        <a href="#" class="btn btn-sm btn-outline-secondary" data-dismiss="modal" aria-label="Bezárás">
          <span class="d-none d-md-inline">Bezárás</span><span class="far fa-times fa-fw" aria-hidden="true"></span>
        </a>
      </div>
      <div class="modal-body"><?=$options['body']?></div>
      <?php if ($options['footer_bar']) { ?>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Bezár</button>
      </div>
      <?php } ?>
    </div>
  </div>
</div>