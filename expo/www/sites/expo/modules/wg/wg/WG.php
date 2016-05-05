<?php
namespace Drupal\wg;
use Drupal\Core\Url;

class WG {
  static public function attach_file($uri) {
    $user = \Drupal::currentUser();

    $file = entity_create('file', array(
                            'uri' => $uri,
                            'uid' => $user->id(),
                            'status' => FILE_STATUS_PERMANENT,
                          ));

    $file->save();
    return $file;
  }

  static public function config($id, array $settings) {
    $config = \Drupal::service('config.factory')->getEditable($id);
    foreach($settings as $k=>$v) {
      $config->set($k, $v);
    }
    $config->save();
    return $config;
  }

  static public function config_get($id, $key) {
    $config = \Drupal::config($id);
    $v = $config->get($key);
    return $v;
  }

  static public function file_get_id_by_uri($uri) {
    $res = \Drupal::entityQuery('file')
           ->condition('uri', $uri)
           ->execute();
    if($res) {
      $fid = array_shift($res);
      return $fid;
    }
    return false;
  }

  static public function file_save_file($src, $dest) {
    $data = file_get_contents($src);
    $file = file_save_data($data, $dest, FILE_EXISTS_REPLACE);
    $fid = $file->id();
    return $fid;
  }

  static public function fetch_file_save_as($url, $dest_uri, $reset = false) {
    if(!file_exists($dest_uri)) {
      if($dest_uri[0] == '/') $file_realpath = $dest_uri;
      else $file_realpath = \Drupal::service('file_system')->realpath($dest_uri);
      DT::fetch_file_save($url, $file_realpath);
    }

  }

  static public function fetch_url($url, $cache=true, $cache_fn = false, $reset = false) {
    if($cache) {
      if($cache ===true && $cache_fn===false) $cache_fn = 'public://cache/';
      else $cache_fn = $cache;
      if(DT::endsWith($cache_fn, '/')) {
        $cache_path = $cache_fn;
        $cache_fn = $cache_path.DT::sanitize($url);
      } else {
        $cache_path = dirname($cache_fn).'/';
      }
      file_prepare_directory($cache_path, FILE_CREATE_DIRECTORY);

      if(!file_exists($cache_fn) || $reset) {
        $data  = DT::_fetch_url_data($url);
        file_put_contents($cache_fn, $data);
      } else {
        $data = file_get_contents($cache_fn);
      }
    } else {
      $data  = DT::_fetch_url_data($url);
    }
    return $data;
  }

  static public function entity_get_field_value($entity, $fieldname, $index = 0, $value_type = 'value') {
    $o0 = $entity->$fieldname;
    $o1 = $o0->get($index);
    $v = null;
    if($o1) {
      $v = $o1->$value_type;
    }
    return $v;
  }

  static public function entity_get_field($entity, $fieldname, $index = 0) {
    $o0 = $entity->$fieldname;
    $o1 = $o0->get($index);
    return $o1;
  }

  static public function entity_get_field_formatted_text($entity, $fieldname, $index = 0) {
    $text = self::entity_get_field_value($entity, $fieldname, $index);
    $format = self::entity_get_field_value($entity, $fieldname, $index, 'format');
    $r = check_markup($text, $format);
    return $r;
  }

  static public function entity_get_field_formatted_text_trim($entity, $fieldname, $index = 0) {
    $text = self::entity_get_field_value($entity, $fieldname, $index);
    $format = self::entity_get_field_value($entity, $fieldname, $index, 'format');
    $r = strip_tags(text_summary($text, $format));
    return $r;
  }

  static public function render_styled_image($uri, $style_name, $render = true) {
    $variables = array(
                   'style_name' => $style_name,
                   'uri' => $uri,
                 );

    $image = \Drupal::service('image.factory')->get($uri);
    if ($image->isValid()) {
      $variables['width'] = $image->getWidth();
      $variables['height'] = $image->getHeight();
    } else {
      $variables['width'] = $variables['height'] = NULL;
    }

    $render_array = [
                      '#theme' => 'image_style',
                      '#width' => $variables['width'],
                      '#height' => $variables['height'],
                      '#style_name' => $variables['style_name'],
                      '#uri' => $variables['uri'],
                    ];
    if($render) $r = render($render_array);
    else $r = $render_array;
    return $r;
  }

  static public function nodeurl($nid, $absolute = false) {
    $url = Url::fromRoute('entity.node.canonical', ['node'=>$nid], ['absolute'=>$absolute]);
    return $url;
  }

  static public function link($text, $url, $link_options) {
    $url->setOptions($link_options);
    $link = \Drupal::l($text, $url);
    return $link;
  }

  static public function xmlTag($tag, $content="", $attributes = array(), $compact = true) {
    if(empty($content) && $compact) {
      $out = "<$tag".self::xmlAttrs($attributes)."/>";
    } else {
      $out = self::xmlStartTag($tag, $attributes);
      $out .= $content;
      $out .= self::xmlEndTag($tag);
    }
    return $out;
  }

  static public function xmlAttrs($attributes = array()) {
    $attr = '';
    if($attributes) {
      foreach($attributes as $n=>$v) {
        if(!is_null($v)) {
          if(is_array($v)) $v = implode(' ', $v);
          $attr .= " $n=\"$v\"";
        }
      }
    }
    return $attr;
  }

  static public function xmlStartTag($tag, $attributes = array()) {
    $attr = self::xmlAttrs($attributes);
    return "<$tag$attr>";
  }

  static public function xmlEndTag($tag) {
    return "</$tag>";
  }

  static public function btGrid($content, $classes=array(), $withContainer=true, $fluid=false) {
    if($withContainer) {
      $container = ($fluid)?'container-fluid' : 'container';
      $classes[] = $container;
    }
    $r = self::xmlTag('div',
                      self::xmlTag('div', $content, ['class'=>'row']),
                      ['class' => implode(' ', $classes)]
                     );
    return $r;
  }


  static public function btGrid_col($content, $ext = '', $xs = 12, $sm = '', $md = '', $lg = '') {
    if(!$sm) $sm = $xs;
    if(!$md) $md = $sm;
    if(!$lg) $lg = $md;

    $classes[] = 'col-xs-'.$xs;
    $classes[] = 'col-sm-'.$sm;
    $classes[] = 'col-md-'.$md;
    $classes[] = 'col-lg-'.$lg;
    $classes[] = $ext;

    $r = self::xmlTag('div', $content, ['class' => implode(' ', $classes)]);
    return $r;
  }


}
