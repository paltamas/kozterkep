<?php
class BlogfriendsJob extends Kozterkep\JobBase {

  public function __construct() {
    Kozterkep\JobBase::__construct();
  }


  public function check() {
    $feeds = sDB['blog_friends'];

    if (count($feeds) > 0) {
      foreach ($feeds as $feed) {

        if ($feed['active'] == 0) {
          continue;
        }

        $res = $this->Curl->get($feed['url']);
        if (!$res) {
          continue;
        }

        $xml = simplexml_load_string($res, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $xml_array = json_decode($json, TRUE);

        if (is_array($xml_array) && count($xml_array) > 0) {

          // Beszúrtuk-e már
          $feed_saved = $this->Mongo->first('blogfriends', ['feed_id' => (int)$feed['id']]);
          $feed_posts = [];
          if ($feed_saved) {
            $feed_posts = $feed_saved['posts'];
          } else {
            $this->Mongo->insert('blogfriends', [
              'feed_id' => (int)$feed['id'],
              'posts' => [],
            ]);
          }

          $feed_posts = $this->compare_posts($xml_array, $feed_posts);

          // Utolsó KT bejegyzés részletei
          $last_post = ['published' => 0];
          $feed_posts = $this->Arrays->sort_by_key($feed_posts, 'published', -1);
          foreach ($feed_posts as $feed_post) {
            if ($feed_post['cat_ok'] == 1) {
              $last_post = $feed_post;
              break;
            }
          }

          $this->Mongo->update('blogfriends', [
            'feed_id' => (int)$feed['id'],
            'posts' => $feed_posts,
            'last_post_time' => $last_post['published'],
            'last_post' => $last_post,
          ], ['feed_id' => (int)$feed['id']]);

        }

      }
    }
  }



  /**
   *
   * Posztok végignézése, nincs-e már meg
   *
   * @param $new_posts_xml
   * @param $saved_posts
   * @return array
   */
  public function compare_posts($new_posts_xml, $saved_posts) {
    foreach ($new_posts_xml as $new_posts) {

      if (isset($new_posts['item']) && is_array($new_posts['item'])) {

        foreach ($new_posts['item'] as $item) {
          if (!isset($item['link']) || !isset($item['title'])) {
            continue;
          }

          // Van-e már
          $found = false;
          foreach ($saved_posts as $key => $saved_post) {
            if ($saved_post['link'] == $item['link']) {
              $found = [$key, $saved_post];
              break;
            }
          }

          $title = strip_tags($item['title']);
          $description = strip_tags($item['description']);
          $timestamp = isset($item['pubDate']) ? strtotime($item['pubDate']) : time();
          $published = $timestamp > time() ? time() : $timestamp;
          $categories = $this->get_rss_categories($item);
          $cat_ok = $categories['kt'] ? 1 : 0;

          if (!$found) {
            // Nincs, létrehozzuk
            $img_url = $this->get_rss_image($item);
            // egyelőre a tartalommal nem foglalkozunk, nem mentjük
            //$content = isset($item['content']) ? strip_tags($item['content']) : '';
            $saved_posts[] = [
              'title' => $title,
              'description' => $description,
              'link' => $item['link'],
              'categories' => $categories['json'],
              'cat_ok' => $cat_ok,
              'created' => time(),
              'published' => $published,
              'image_url' => $img_url
            ];
          } else {
            // Van, frissítjük, ha kell, vagy 5 napon belüli
            $saved = $found[1];
            if ($saved['title'] != $title || $saved['description'] != $description
              || $published > strtotime('-5 days')) {
              $saved_posts[$found[0]] = array_merge($saved_posts[$found[0]], [
                'title' => $title,
                'description' => $description,
                'cat_ok' => $cat_ok,
              ]);
            }
          }

        }
      }
    }

    return $saved_posts;
  }




  private function get_rss_image($item) {
    $img_url = '';
    $img_tag_name = 'img';

    // Szép helyen van
    if (isset($item['enclosure']) && isset($item['enclosure']['@url']) && isset($item['enclosure']['@type']) && strpos($item['enclosure']['@type'], 'image/') == 0) {
      $img_url = $item['enclosure']['@url'];
    } elseif (isset($item['media:thumbnail']) && count($item['media:thumbnail']) > 0) {
      foreach ($item['media:thumbnail'] as $media) {
        if (!isset($media['@width'])) {
          if (is_array($media) && isset($media['@url'])) {
            $img_url = $media['@url'];
          } elseif (is_string($media)) {
            $img_url = $media;
          }
          break;
        }
        if ($media['@width'] > 200) {
          $max_width = $media['@width'];
          $img_url = $media['@url'];
        }
      }

    } else {
      // NEM Olyan szép helyen
      $content = isset($item['content']) ? $item['content'] : isset($item['content:encoded']) ? $item['content:encoded'] : false;
      if ($content) {
        preg_match_all('/<' . $img_tag_name . ' [^>]*src=["|\']([^"|\']+)/i', $content, $matches);
        if (isset($matches[1][0]) && filter_var($matches[1][0], FILTER_VALIDATE_URL)) {
          $img_url = $matches[1][0];
        }
      }
    }

    return $img_url;
  }


  private function get_rss_categories($item) {
    $found = false;
    if (isset($item['category']) && is_array($item['category'])) {
      $categories = [];
      foreach ($item['category'] as $cat) {
        $name = isset($cat['@']) ? $cat['@'] : $cat;
        if (!$found && in_array($name, ['Köztérkép', 'köztérkép', 'kozterkep'])) {
          $found = true;
        }
        $categories[] = $name;
      }
      $resp = [
        'kt' => $found,
        'json' => count($categories) > 0 ? json_encode($categories) : ''
      ];
    } else {
      $resp = [
        'kt' => 0,
        'json' => ''
      ];
    }

    return $resp;
  }

}