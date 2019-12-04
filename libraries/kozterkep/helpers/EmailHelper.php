<?php
namespace Kozterkep;

class EmailHelper {

  public function __construct($app_config = false) {
    $this->Text = new TextHelper();
    $this->MC = new MemcacheComponent();
    $this->apikey = C_WS_SENDGRID['apikey'];
    $this->site_email = $app_config ? $app_config['site_email'] : C_WS_SENDGRID['default_from_email'];
    $this->title = $app_config ? $app_config['title'] : C_WS_SENDGRID['default_from_name'];
    $this->site_url = $app_config ? $app_config['url'] : CORE['BASE_URL'];
  }

  /**
   *
   * Email küldés
   *
   * @param $options
   * @return bool
   */
  public function send($options) {
    if (!$options
      || (!isset($options['to']) && !isset($options['user_id']))
      || !isset($options['subject'])
      || !isset($options['body'])) {
      return false;
    }

    $options = (array)$options + [
      'user_id' => 0,
      'name' => '',
      'to' => '',
      'reply_to' => $this->site_email,
      'from_email' => $this->site_email,
      'from_name' => $this->title,
      'template' => 'simple',
    ];

    if ($options['user_id'] > 0) {
      $user = $this->MC->t('users', (int)$options['user_id']);
      $unsub_key = sha1($user['id']) . '.' . sha1($user['email']);
      $options['name'] = $options['name'] == '' ? $user['name'] : $options['name'];
      $options['to'] = $options['to'] == '' ? $user['email'] : $options['to'];
    } else {
      $unsub_key = '';
    }


    // Devről csak nekik mehet email
    if (CORE['ENV'] == 'dev'
      && !in_array($options['to'], ['paltamas@gmail.com', 'pt@kozterkep.hu', 'irmerix@gmail.com'])) {
      return true;
    }

    if ($options['to'] == '') {
      return false;
    }

    if (CORE['ENV'] == 'dev') {
      // Hogy tutira tudjam, amikor kapok valamit, honnan.
      $options['subject'] = 'DEV: ' . $options['subject'];
    }

    // Ezekkel megyünk
    $from = new \SendGrid\Email($options['from_name'], $options['from_email']);
    $to = new \SendGrid\Email($options['name'], $options['to']);
    $body = texts('emailtemplates/' . $options['template'], [
      'email.preheader' => $this->Text->truncate($options['body'], 200) . '...',
      'email.body' => $options['body'],
      'email.subject' => $options['subject'],
      'site.url' => $this->site_url,
      'unsub_key' => $unsub_key,
    ]);
    $content = new \SendGrid\Content("text/html", $body);

    $mail = new \SendGrid\Mail($from, $options['subject'], $to, $content);

    $mail->setReplyTo(new \SendGrid\Email($options['reply_to'], $options['reply_to']));

    // Klientúra
    $sg = new \SendGrid($this->apikey);

    // a Küldés maga
    $response = $sg->client->mail()->send()->post($mail);

    // Sikerünk volt-e
    return @$response->statusCode() == 202 ? true : false;
  }



  public function artpiece_html($artpiece, $options = []) {
    $options = (array)$options + [
      'photo_size' => 4,
      'title' => true,
      'details' => false,
      'place' => '',
      'year' => '',
    ];

    $s = '<div style="text-align:center; margin-bottom: 20px; padding: 15px; background: #efede5; border-radius: 20px;">';

    $s .= '<a href="' . CORE['BASE_URL'] . '/' . $artpiece['id'] . '" style="text-decoration: none;">';
    $s .= '<img src="' . C_WS_S3['url'] . 'photos/' . $artpiece['photo_slug'] . '_' . $options['photo_size'] . '.jpg" alt="' . $artpiece['title'] . ' műlap borítóképe" border="0" style="border-radius: 8px; border: 6px solid #ffffff; width: 95%">';
    $s .= '</a>';

    if ($options['title']) {
      // Cím
      $s .= '<div style="margin-top: 7px; font-weight: bold;"><a href="' . CORE['BASE_URL'] . '/' . $artpiece['id'] . '" style="color: #c95b12; text-decoration: none;">' . $artpiece['title'] . '</a></div>';
    }

    if ($options['details']) {
      $s .= '<div style="margin-top: 3px;">';
      // Hely
      $s .= $options['place'];
      // Év, ha
      $s .= $options['year'] != '' ? ', ' . $options['year'] : '';
      $s .= '</div>';

      // Alkotó
      $artists = _json_decode($artpiece['artists']);
      if (@$artists[0]['id'] > 0) {
        $artist = $this->MC->t('artists', $artists[0]['id']);
        $s .= '<div style="margin-top: 3px;">' . $artist['name'] . '</div>';
      }
    }

    $s .= '</div>';

    return $s;
  }



  public function artpiece_toplist($artpieces) {
    $s = '';
    if (count($artpieces) > 0) {
      $s .= '<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;" width="100%">
        <thead>
        <tr>
          <th style="border-bottom: 2px solid #dedede; padding: 5px;">#</th>
          <th style="border-bottom: 2px solid #dedede; padding: 5px;">Műlap</th>
          <th style="border-bottom: 2px solid #dedede; padding: 5px;">Heti</th>
          <th style="border-bottom: 2px solid #dedede; padding: 5px;">Össz.</th>
        </tr>
        </thead>
        <tbody>';

      $i = 0;
      foreach ($artpieces as $artpiece) {
        $i++;
        $s .= '<tr>';

        $s .= '<td style="border-bottom: 1px solid #dedede; padding: 5px;">' . $i . '.</td>';
        $s .= '<td style="border-bottom: 1px solid #dedede; padding: 5px;">';
        $s .= '<a href="' . CORE['BASE_URL'] . '/' . $artpiece['id'] . '" style="text-decoration: none;">';
        $s .= '<img src="' . C_WS_S3['url'] . 'photos/' . $artpiece['photo_slug'] . '_7.jpg" border="0" style="border-radius: 4px; border: 1px solid #cccccc; width: 20px;" align="left">';
        $s .= '</a> ';
        $s .= '<strong style="margin-left: 5px;"><a href="' . CORE['BASE_URL'] . '/' . $artpiece['id'] . '" style="color: #c95b12; text-decoration: none;">' . $artpiece['title'] . '</a></strong>';
        $s .= '</td>';

        $s .= '<td style="text-align: center; border-bottom: 1px solid #dedede; padding: 5px;">' . _n($artpiece['view_week']) . '</td>';
        $s .= '<td style="text-align: center; border-bottom: 1px solid #dedede; padding: 5px;">' . _n($artpiece['view_total']) . '</td>';

        $s .= '</tr>';
      }

      $s .= '</tbody>
        </table>';
    }
    return $s;
  }


}
