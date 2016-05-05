<?php
class MXML {
  static $content_encode = FALSE;
  static $stacks = array();

  function content_encode($string) {
    return str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
    return philsXMLClean($string);
  }

  /**
   * simple tag
   */
  static public function tag($tag, $content="", $attributes = array()) {
    if(empty($content)) {
      $out = "<$tag".self::attrs($attributes)."/>";
    } else {
      $out = self::startTag($tag, $attributes);
      if(self::$content_encode) $content = self::content_encode($content);
      $out .= $content;
      $out .= self::endTag();
    }
    return $out;
  }

  /**
   * start tag, store tag to stacks
   */
  static public function startTag($tag, $attributes = array()) {
    $attr = self::attrs($attributes);
    array_push(self::$stacks, $tag);
    return "<$tag$attr>";
  }

  /**
   * end tag, get tag from stacks
   */
  static public function endTag() {
    $tag = array_pop(self::$stacks);
    return "</$tag>";
  }

  /**
   * attrs
   * support list (array)
   */
  static public function attrs($attributes = array()) {
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

  /** functions **/
  function write($xmlstring, $file='', $pretty = FALSE) {
    if($pretty) {
      $xmlstring = self::prettyXML($xmlstring);
    } else {
      // just test if valid
      $xml_obj = new SimpleXMLElement($xmlstring);
    }

    if($xmlstring {1} != '?') {
      $xmlstring = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n".$xmlstring;
    }
    if($file) {
      file_put_contents($file, $xmlstring);
    }
    return $xmlstring;
  }

  function prettyXML($xml) {
    if($xml instanceOf SimpleXMLElement) {
      $xml_obj = $xml;
    } else {
      $xml_obj = new SimpleXMLElement($xml);
    }

    $string = $xml_obj->asXML();
    $string = self::xmlunescape($string);

    $string =str_replace("<","\n<",$string);
    $string =str_replace(">",">\n",$string);
    $string =preg_replace("%\n{2,}%","\n",$string);
    $string =preg_replace("/>\s*</",">\n<",$string);
    $xmlArray = explode("\n",$string);

    $currIndent = 0;

    $string = "";
    foreach($xmlArray as $element) {
      if(preg_match('/^<\?/U',$element)) {  // ignore inst;
      }
      /** find open only tags... add name to stack, and print to string
       */

      else if(preg_match('/^<([\w])+([^>]*)((?<!\/)>)$/U',$element)) {
        $string .=  str_repeat(' ', $currIndent) . $element . "\n";
        $currIndent += 2;
      }

      /**
       * find standalone closures, decrement currindent, print to string
       */
      elseif(preg_match('/^<\/.+>$/',$element)) {
        $currIndent -= 2;
        if($currIndent < 0) $currIndent = 0;
        $string .=  str_repeat(' ', $currIndent) . $element . "\n";
      }
      /**
       * find open/closed tags on the same line print to string
       */
      else {
        $string .=  str_repeat(' ', $currIndent) . $element . "\n";
      }
    }

    return $string;
  }

  function xmlunescape($str) {
    $zz=array_values(get_html_translation_table());
    $ttbl = array();
    $i=0;
    foreach($zz as $z) {
      $ttbl['xdxdxdxd'.$i."xdxdxd"] = $z;
      $i++;
    }
    $str = str_replace(array_values($ttbl), array_keys($ttbl), $str);
    $str = html_entity_decode($str,null,'UTF-8');
    $str = str_replace(array_keys($ttbl), array_values($ttbl), $str);
    return $str;
  }

}
