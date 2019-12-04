<?php

namespace Kozterkep;

class TextHelper {

  public function __construct() {
    $this->Html = new HtmlHelper();
  }


  /**
   *
   * Szöveg formázás
   * Ha jön a source_id, akkor annak a forrásnál is ugyanennek kell lennie!
   *
   * @param $string
   * @param array $options
   * @return null|string|string[]
   */
  public function format($string, $options = []) {
    $options = (array)$options + [
      'nl2br' => true,
      'format' => true,
      'intro' => false,
      'strip_tags' => true,
      'allowed_tags' => '',
      'source_id' => '',
      'highlight' => false,
      'highlight_excerpt' => false,
      'class' => false,
    ];

    if ($options['strip_tags']) {
      $string = strip_tags(html_entity_decode($string), $options['allowed_tags']);
    }

    if ($options['nl2br']) {
      $string = nl2br($string, false);
    }

    if ($options['format']) {
      // Vastagítás
      //$string = preg_replace('/\*(.*?)\*/', "<b>$1</b>", $string);
      // Forrásolás
      $string = preg_replace('/\[[0-9]*\]/', "<sup><a href=\"#forras-" . $options['source_id'] . "$0\">$0</a></sup>", $string);
    } else {
      // kiszedjük, hogy ne rondítson
      //$string = preg_replace('/\*(.*?)\*/', "$1", $string);
    }

    if ($options['intro']) {
      $length = $options['intro'] > 0 ? $options['intro'] : 150;
      $string = $this->read_more($string, $length);
    }

    // Intrózásnál nem emelünk ki szöveget, mert nem tudjuk, látszik-e
    if ($options['highlight'] && $options['highlight'] != ''
      && !$options['intro']) {
      $string = preg_replace('/\w*?' . $options['highlight'] . '\w*/i', '<span class="bg-highlighted">$0</span>', $string);
      if ($options['highlight_excerpt']) {
        $length = is_numeric($options['highlight_excerpt']) ? $options['highlight_excerpt'] : 100;
        $string = $this->excerpt($string, $options['highlight'], $length);
      }
    }

    if ($options['class'] && $options['class'] != '' && $string != '') {
      $string = '<div class="' . $options['class'] . '">' . PHP_EOL . $string . PHP_EOL . '</div>';
    }

    return $string;
  }

  /**
   *
   * Ez CakePHP :) köszönjük!
   * String / Text Utility
   * https://book.cakephp.org/3.0/en/core-libraries/text.html
   *
   * @param $text
   * @param $phrase
   * @param int $radius
   * @param string $ellipsis
   * @return null|string|string[]
   */
  public function excerpt($text, $phrase, $radius = 100, $ellipsis = '...') {
    if (empty($text) || empty($phrase)) {
      return self::truncate($text, $radius * 2, array('ellipsis' => $ellipsis));
    }

    $append = $prepend = $ellipsis;

    $phraseLen = mb_strlen($phrase);
    $textLen = mb_strlen($text);

    $pos = mb_strpos(mb_strtolower($text), mb_strtolower($phrase));
    if ($pos === false) {
      return mb_substr($text, 0, $radius) . $ellipsis;
    }

    $startPos = $pos - $radius;
    if ($startPos <= 0) {
      $startPos = 0;
      $prepend = '';
    }

    $endPos = $pos + $phraseLen + $radius;
    if ($endPos >= $textLen) {
      $endPos = $textLen;
      $append = '';
    }

    $excerpt = mb_substr($text, $startPos, $endPos - $startPos);
    $excerpt = $prepend . trim($excerpt) . $append;

    return $excerpt;
  }

  /**
   *
   * Linkelt forrás
   * Ha jön a source ID, akkor annak a text-nél ugyanennek kell lennie!
   *
   * @param $string
   * @param $options
   * @return null|string|string[]
   */
  public function format_source($string, $options = []) {
    $options = (array)$options + [
      'source_id' => '',
    ];
    $string = nl2br($string, false);
    $string = preg_replace('/\[[0-9]*\]/', "<span id=\"forras-" . $options['source_id'] . "$0\">$0</span> ", $string);
    return $string;
  }


  public function read_more($string, $length = 300, $format = false, $format_options = []) {
    $full_string = $format ? $this->format($string, $format_options) : $string;

    if (mb_strlen($string) < $length * 1.3) {
      return $full_string;
    }

    $id = 'MR-' . uniqid();

    $s = '';

    $short = $this->truncate($full_string, $length, [
      'strip_tags' => true
    ]);

    $s .= '<span id="full-' . $id . '-intro">';
    $s .= $short;
    $s .= $this->Html->link('Teljes szöveg', '#', [
      'icon_right' => 'chevron-down',
      'ia-showfull' => '#full-' . $id,
      'class' => 'ml-2 text-nowrap'
    ]);
    $s .= '</span>';

    $s .= '<span class="d-none" id="full-' . $id . '">' . $full_string;

    $margin = substr($full_string, -1) != '>' ? 'ml-3 ' : '';

    $s .= $this->Html->link('Kevesebb szöveg', '#', [
      'icon_right' => 'chevron-up',
      'ia-showless' => '#full-' . $id,
      'class' => $margin . 'text-nowrap'
    ]);

    $s .= '</span>';

    return $s;
  }


  public function read_more_deprecated($string, $length = 300, $format = false, $format_options = []) {
    $full_string = $format ? $this->format($string, $format_options) : $string;

    if (mb_strlen($string) < $length) {
      return $full_string;
    }

    $id = 'MR-' . uniqid();

    $s = '<div class="accordion" id="accordion-' . $id . '">';
    $s .= '<div class="collapse show my-0 py-0" id="intro-' . $id . '" data-collapse-pair="#fulltext-' . $id . '">';
    $s .= $this->truncate($full_string, $length);
    $s .= $this->Html->link('Teljes szöveg', '#intro-' . $id, [
      'icon' => 'chevron-down',
      'data-toggle' => 'collapse',
      'class' => 'ml-2 text-nowrap'
    ]);
    $s .= '</div>'; // intro
    $s .= '<div class="collapse" id="fulltext-' . $id . '" data-collapse-pair="#intro-' . $id . '">';
    $s .= $full_string;
    $s .= $this->Html->link('Kevesebb szöveg', '#fulltext-' . $id, [
      'data-toggle' => 'collapse',
      'icon' => 'chevron-up',
      'class' => 'd-block ml-2',
    ]);
    $s .= '</div>'; // fulltext
    $s .= '</div>'; // accordion

    return $s;
  }

  /**
   *
   * Kutatás a tökéletes PHP-ban is működő RegExp után...
   * ez kb minden helyzetet lekezelt a régi KT-n, nyolcvan módosítás után.
   * A végső vessző és pont levágás picit gagyi, de garantált.
   *
   * Ideje lenne megtanulni regexpül.
   *
   * @param $text
   * @param array $options
   * @return null|string|string[]
   */
  public static function auto_link($text, $options = []) {
    $options = (array)$options + ['target' => '_self'];
    $target = isset($options['target']) ? $options['target'] : '_self';
    $reg_exUrl = '/(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/i';
    if (preg_match_all($reg_exUrl, $text, $urls)) {
      if (is_array(@$urls[0]) && count($urls[0]) > 0) {
        foreach ($urls[0] as $link) {
          $last_char = substr($link, -1);
          if (in_array($last_char, array(',', '.', '!', '?'))) {
            // Le kell vágni a linkről, aztán a szövegbe odatenni
            $trimmed_char = $last_char;
            $link = rtrim($link, $last_char);
          } else {
            $trimmed_char = '';
          }
          $parse = parse_url($link);
          $domain = str_replace('www.', '', $parse['host']);
          $text = str_replace(
            $link, '<a href="' . $link . '" target="' . $target . '" title="' . $link . '">'
            . $domain . '</a>' . $trimmed_char, $text
          );
        }
      }
    }

    return $text;
  }

  /**
   *
   * szlagosító
   * THX https://gist.github.com/sgmurphy/3098978
   *
   * @param $str
   * @param $options
   * @return string
   */
  public function slug($str, $options = []) {

    // Make sure string is in UTF-8 and strip invalid UTF-8 characters
    $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

    $options = (array)$options + [
        'delimiter' => '-',
        'limit' => null,
        'lowercase' => true,
        'replacements' => [],
        'transliterate' => true,
      ];

    $char_map = array(
      // Latin
      'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
      'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
      'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
      'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
      'ß' => 'ss',
      'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
      'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
      'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
      'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
      'ÿ' => 'y',
      // Latin symbols
      '©' => '(c)',
      // Greek
      'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
      'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
      'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
      'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
      'Ϋ' => 'Y',
      'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
      'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
      'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
      'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
      'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
      // Turkish
      'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
      'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',
      // Russian
      'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
      'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
      'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
      'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
      'Я' => 'Ya',
      'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
      'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
      'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
      'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
      'я' => 'ya',
      // Ukrainian
      'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
      'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
      // Czech
      'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
      'Ž' => 'Z',
      'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
      'ž' => 'z',
      // Polish
      'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
      'Ż' => 'Z',
      'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
      'ż' => 'z',
      // Latvian
      'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
      'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
      'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
      'š' => 's', 'ū' => 'u', 'ž' => 'z'
    );

    // Make custom replacements
    $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

    // Transliterate characters to ASCII
    if ($options['transliterate']) {
      $str = str_replace(array_keys($char_map), $char_map, $str);
    }

    // Replace non-alphanumeric characters with our delimiter
    $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

    // Remove duplicate delimiters
    $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

    // Truncate slug to max. characters
    $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

    // Remove delimiter from ends
    $str = trim($str, $options['delimiter']);

    return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
  }


  /**
   *
   * Bevezető
   *
   * @param $str
   * @param int $maxLength
   * @param bool $format
   * @param string $ellipsis
   * @return null|string|string[]
   */
  public function truncate($str, $maxLength = 100, $format_options = false, $ellipsis = '...') {
    // Szóköz figyelembevétel nélkül
    if ($format_options == 'substr') {
      if (mb_strlen($str) > $maxLength) {
        return substr($str, 0, $maxLength) . $ellipsis;
      } else {
        return $str;
      }
    }

    // Szóköz figyelembevételével
    $startPos = 0;
    if (mb_strlen($str) > $maxLength) {
      $intro = mb_substr($str, $startPos, $maxLength - 3);
      $lastSpace = mb_strrpos($intro, ' ');
      $intro = mb_substr(strip_tags($intro), 0, $lastSpace);
      $intro .= $ellipsis;
      $format_options = (array)@$format_options + ['nl2br' => false];
      $intro = $this->format($intro, $format_options);
    } else {
      $intro = $this->format($str, $format_options);
    }

    return $intro;
  }


  /**
   *
   * Szótömbbé alakított szövegeket összevet
   * https://github.com/paulgb/simplediff/blob/master/php/simplediff.php
   *
   * @param $old
   * @param $new
   * @return array
   */
  public function diff($old, $new) {
    $matrix = array();
    $maxlen = 0;
    foreach ($old as $oindex => $ovalue) {
      $nkeys = array_keys($new, $ovalue);
      foreach ($nkeys as $nindex) {
        $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ?
          $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
        if ($matrix[$oindex][$nindex] > $maxlen) {
          $maxlen = $matrix[$oindex][$nindex];
          $omax = $oindex + 1 - $maxlen;
          $nmax = $nindex + 1 - $maxlen;
        }
      }
    }
    if ($maxlen == 0) return array(array('d' => $old, 'i' => $new));
    return array_merge(
      $this->diff(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
      array_slice($new, $nmax, $maxlen),
      $this->diff(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen))
    );
  }


  /**
   *
   * HTML formázza a diffet
   * https://github.com/paulgb/simplediff/blob/master/php/simplediff.php
   * @todo: sortörés...
   *
   * @param $old
   * @param $new
   * @return string
   */
  public function html_diff($old, $new, $options = array()) {
    $options = (array)$options + [
      'strip_tags' => false,
    ];

    if ($options['strip_tags']) {
      $old = str_replace(PHP_EOL, '<br>', strip_tags($old));
      $new = str_replace(PHP_EOL, '<br>', strip_tags($new));
    }

    // Néhány replace kell, mert kezelhetetlen a dolog
    // @todo: ha ez itt elharapódzik, akkor kiszervezni fgv-be, vagy jobb megoldást találni...
    $replacements = array('&#34;' => '"');
    foreach ($replacements as $from => $to) {
      $old = str_replace($from, $to, $old);
      $new = str_replace($from, $to, $new);
    }

    $ret = '';
    $diff = $this->diff(preg_split("/[\s]+/", $old), preg_split("/[\s]+/", $new));
    foreach ($diff as $k) {
      if (is_array($k))
        $ret .= (!empty($k['d']) ? "<del>" . implode(' ', $k['d']) . "</del> " : '') .
          (!empty($k['i']) ? "<ins>" . implode(' ', $k['i']) . "</ins> " : '');
      else $ret .= $k . ' ';
    }
    return $ret;
  }


  /**
   *
   * Értékes szavakat megtartó logika
   *
   * @param $string
   * @param array $options
   * @return string
   */
  public function value_words($string, $options = []) {
    $options = (array)$options + [
        'ignorandus' => [], // kihagyott szavak
        'lower' => true, // kisbetűssé alakítás
        'min_length' => 3, // szavak min. elvárt hossza
      ];

    // Kihagyandók
    if (is_array($options['ignorandus']) && count($options['ignorandus']) > 0) {
      $string = str_replace($options['ignorandus'], '', $string);
    }

    // Kiszedem a rövidebb szavakat
    if ($options['min_length'] && $options['min_length'] > 0) {
      $words = preg_split("/\r\n|\n|\r| /", $string);
      $string_ = '';
      foreach ($words as $word) {
        if (mb_strlen($word) > $options['min_length']) {
          $string_ .= $word . ' ';
        }
      }
      $string = trim($string_);
    }

    if ($options['lower']) {
      $string = mb_strtolower($string);
    }

    return trim($string);
  }


}
