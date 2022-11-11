<?php
function auth () {
  $users = C_USERS;

  // Kilép
  if (isset($_GET['kilepes'])) {
    $_SESSION['user'] = false;
    $_COOKIE[C_SESSION['user']] = 0;
    header("location: /");
  }



  if (@$_POST['user'] != '' && @$_POST['pass'] != '') {

    // Most lép be

    if (isset($users[$_POST['user']]) && $users[$_POST['user']] == $_POST['pass']) {
      $_SESSION['user'] = $_POST['user'];

      $token = sha1($_POST['user'] . '-' . $_POST['pass']);

      setcookie(
        C_SESSION['user'],
        $token,
        time() + C_SESSION['user_expiry'], '/',
        C_ENV['domain'],
        true, // secure
        true // httponly
      );

      header("location: /");
    } else {

      die('Hibas belepesi adatok. <a href="/">vissza</a>');

    }

  } elseif (isset($_COOKIE[C_SESSION['user']]) && $_COOKIE[C_SESSION['user']] != 0) {

    // Van cookie

    if (!isset($_SESSION['user'])) {
      // De nincs session; megkeressük és megírjuk
      foreach ($users as $user => $pass) {
        if (sha1($user . '-' . $pass) == $_COOKIE[C_SESSION['user']]) {
          $_SESSION['user'] = $user;
          break;
        }
      }
    }

    return $_SESSION['user'];

  } elseif (isset($_SESSION['user']) && $_SESSION['user'] != false) {

    // Valami miatt nincs cookie, de van session

    $token = sha1($_SESSION['user'] . '-' . $users[$_SESSION['user']]);

    setcookie(
      C_SESSION['user'],
      $token,
      time() + C_SESSION['user_expiry'], '/',
      C_ENV['domain'],
      true, // secure
      true // httponly
    );

    return $_SESSION['user'];
  }

  return false;
}