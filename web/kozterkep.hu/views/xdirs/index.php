<div class="card-deck">

  <div class="card bg-light border-0 shadow mb-5">
    <div class="card-body p-4">
      <h2 class="display-1 my-3 text-primary">Mi az Adattár?</h2>
      <p class="lead font-weight-bold">A Köztérkép felhasználói mappáit, valamint azokat az al-adatbázisainkat éred el itt, amelyek segíthetnek a köztéri alkotásokkal kapcsolatos kutatómunkádban.</p>
      <p class="lead">Az interneten sokminden múlandó, de mi szeretnénk megőrizni azokat a forrásokat, amik értékesek számunkra. Sokat dolgozunk azért, hogy a világhálón alátámasztott és minőségi adatok keringjenek. Ez a hely is azért jött létre, hogy ezek az adatbázisok ne hulljanak a nekik szánt fekete lyukakba. Na, meg az optimizmus.</p>
    </div>
  </div>

  <div class="card bg-dark text-white border-0 shadow mb-5">
    <div class="card-body p-4">

      <a href="/adattar/lexikon">
        <img src="/img/etc/kmml-cropped.jpg" class="img-fluid rounded">
      </a>

      <h2 class="display-1 my-3">
        <?=$app->Html->link('Lexikon', '/adattar/lexikon', [
          'icon_right' => 'arrow-right',
          'class' => 'text-light',
        ])?>
      </h2>
      <p class="lead">Az Enciklopédia Kiadó által 3 kötetben megjelentetett Kortárs Magyar Művészeti Lexikon szócikkeinek digitális változata. <?=$app->Html->link('Megnézem', '/adattar/lexikon', ['icon_right' => 'arrow-right'])?></p>

    </div>
  </div>

</div>

<div class="card-deck">

  <div class="card bg-dark text-white border-0 shadow mb-5">
    <div class="card-body p-4">

      <a href="/mappak/attekintes">
        <img src="/img/etc/mappak.jpg" class="img-fluid rounded">
      </a>

      <h2 class="display-1 my-3">
        <?=$app->Html->link('Mappák', '/mappak/attekintes', [
          'icon_right' => 'arrow-right',
          'class' => 'text-light',
        ])?>
      </h2>
      <p class="lead">Felhasználóink által feltöltött állományok, amelyeket itt-ott linkelünk, vagy épp műlapok helyett itt mutatjuk be, mert oda nem illenek. <?=$app->Html->link('Megnézem', '/mappak/attekintes', ['icon_right' => 'arrow-right'])?></p>
    </div>
  </div>

  <div class="card bg-gray-kt border-0 shadow mb-5">
    <div class="card-body p-4">

      <a href="/adattar/konyvter">
        <img src="/img/etc/konyvter.jpg" class="img-fluid rounded">
      </a>

      <h2 class="display-1 my-3">
        <?=$app->Html->link('Könyvtér', '/adattar/konyvter', [
          'icon_right' => 'arrow-right',
          'class' => 'text-dark',
        ])?>
      </h2>
      <p class="lead">Köztéri művészettel, alkotókkal kapcsolatos könyveink listája. Ha keresel valamit, itt megtudod, ki segíthet neked. <?=$app->Html->link('Megnézem', '/adattar/konyvter', ['icon_right' => 'arrow-right'])?></p>
    </div>
  </div>

</div>

<div class="card-deck">
  <div class="card bg-gray-kt border-0 shadow mb-5">
    <div class="card-body p-4">

      <a href="/adattar/hosi-emlek">
        <img src="/img/etc/hosi-emlek.jpg" class="img-fluid rounded">
      </a>

      <h2 class="display-1 my-3">
        <?=$app->Html->link('Hősi Emlék', '/adattar/hosi-emlek', [
          'icon_right' => 'arrow-right',
          'class' => 'text-dark',
        ])?>
      </h2>
      <p class="lead">Az Enciklopédia Kiadó által 3 kötetben megjelentetett Kortárs Magyar Művészeti Lexikon szócikkeinek digitális változata.</p>
    </div>
  </div>

  <div class="card bg-dark text-white border-0 shadow mb-5">
    <div class="card-body p-4">

      <a href="/adattar/kutatasi-partnerek">
        <img src="/img/etc/szepmuveszeti.jpg" class="img-fluid rounded">
      </a>

      <h2 class="display-1 my-3">
        <?=$app->Html->link('Kutatási partnerek', '/adattar/kutatasi-partnerek', [
          'icon_right' => 'arrow-right',
          'class' => 'text-light',
        ])?>
      </h2>
      <p class="lead">Ha saját al-adatbázisaink nem segítenek a kutatásban, kérj külső segítséget! <?=$app->Html->link('Megnézem', '/adattar/kutatasi-partnerek', ['icon_right' => 'arrow-right'])?></p>

    </div>
  </div>
<div>