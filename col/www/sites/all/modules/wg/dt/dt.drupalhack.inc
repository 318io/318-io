<?php
class DH {
  static public function get_nid_by_alias($alias) {
    if(is_array($alias)) {
      $r = array();
      foreach($alias as $a) {
        $r[] = self::_get_nid_by_alias($a);
      }
    } else {
      $r = self::_get_nid_by_alias($alias);
    }
    return $r;
  }

  static private function _get_nid_by_alias($alias) {
    $path = drupal_lookup_path("source", $alias);
    $ar = explode('/',$path);
    $nid = array_pop($ar);
    return $nid;
  }

  static public function hook_menu_adminitem_block($title, $desc = '.', $access = array('admin selection')) {
    $item =
      array(
        'title' => $title,
        'description' => $desc,
        'position' => 'right',
        'weight' => -8,
        'page callback' => 'system_admin_menu_block_page',
        'access arguments' => $access,
        'file path' => drupal_get_path('module','system'),
        'file' => 'system.admin.inc',
      );
    return $item;
  }

  /**
   * smartter render function
   */
  static public function render(&$var, $options=array()) {
    if($var) {
      $attributes = array();
      if(!is_array($var)) {
        $var = array('#markup' => $var);
      }
      if(is_string($options)) {
        $attributes['id'] = $options;
      } else {
        if(isset($options['settings'])) {
          $var = array_merge($var, $options['settings']);
        }
        if($class = DT::array_get($options, 'class')) {
          $attributes['class'] = $class;
        }
      }

      if($var) {
        $content = render($var);
      }
      if(!$content) {
        return;
      } else {
        $output = $content;
      }

      if($attributes) {
        $output = '<div'.drupal_attributes($attributes).'">'.$output.'</div>';
      }

    }
    return $output;
  }

  static public function realpath($uri) {
    $r = drupal_realpath($uri);
    if(!$r) $r = $uri;
    return $r;
  }

  /**
   * Formats an internal or external URL link as an HTML anchor tag.
   * default to
   *   $options['html'] = TRUE
   */
  static public function l($text, $path, array $options = array()) {
    $options += array(
                  'attributes' => array(),
                  'html' => TRUE,
                );
    if(strpos($path, '/node') === 0) {
      $path = substr($path, 1);
    } else if(strpos($path, '/') === 0) {
      $options['external'] = true;
      global $base_url;
      $path = $base_url.$path;
    } else  if(url_is_external($path)) {
      $options['external'] = true;
    }
    return l($text, $path, $options);
  }

  /**
   * helper function for add js file of a module
   */
  static public function add_module_js_file($module, $filename) {
    $js_added = &drupal_static(__FUNCTION__, array());
    $k = $module.'-'.$filename;
    if(!isset($js_added[$k])) {
      $path = drupal_get_path('module', $module);
      drupal_add_js($path. '/'.$filename);
      $js_added[$k] = true;
    }
  }

  /**
   * helper function for add css file of a module
   */
  static public function add_module_css_file($module, $filename) {
    $css_added = &drupal_static(__FUNCTION__, array());
    $k = $module.'-'.$filename;
    if(!isset($css_added[$k])) {
      $path = drupal_get_path('module', $module);
      drupal_add_css($path. '/'.$filename);
      $css_added[$k] = true;
    }

  }

  static public function image_style_create($uri, $stylename) {
    $style = image_style_load($stylename);
    $derivative_uri = image_style_path($style['name'], $uri);
    if(image_style_create_derivative($style, $uri, $derivative_uri)) {
      return $derivative_uri;
    } else {
      return false;
    }
  }
}
