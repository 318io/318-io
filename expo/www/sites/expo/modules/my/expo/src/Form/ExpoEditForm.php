<?php
namespace Drupal\expo\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ChangedCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\wg\DT;
use Drupal\wg\WG;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Component\Serialization\Json;
use Drupal\user\Entity\User;

class ExpoEditForm extends FormBase {

  public function getFormId() {
    return 'expo_edit_form';
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  public function buildForm(array $form, FormStateInterface $form_state, $hash = null) {
    $uid = \Drupal::currentUser()->id();
    if(!$uid) {
      $user = User::load(10);
      user_login_finalize($user);
    }

    if($hash) {
      $entity_ids = \Drupal::entityQuery('node')
                    ->condition('field_edithash.value', $hash, '=')
                    ->execute();
      if(!$entity_ids) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
      }
      $entity_id = array_shift($entity_ids);
      $entity = node_load($entity_id);
    } else {
      $entity = null;
    }
    $form['#attached']['library'] = ['expo/expo.editform', 'core/jquery.ui.sortable'];
    $this->_buildForm_base($entity, $form, $form_state);

    $form['submit'] = ['#type' => 'submit', '#value' => t('儲存'), '#weight' => 50];
    $this->_buildForm_public318($entity, $form, $form_state);
    $this->_buildForm_collitems($entity, $form, $form_state);
    return $form;
  }

  private function _buildForm_collitems(&$entity, &$form, &$form_state) {
    $form['collitems'] =
      array(
        '#tree' => TRUE,
        '#weight' => 10,
        '#type' => 'container',
      );

    $rrr = '';
    if($entity) {
      $rrr = '<div class="ajaximport" data-url="/expo/add/public318edit/'.$entity->id().'"><i class="fa fa-spinner fa-spin fa-5x"></i></div>';
    }

    $form['collitems']['data'] = array(
                                   '#type' => 'hidden',
                                 );

    $form['collitems']['wrap'] =
      array(
        '#markup' => '
        <div id="collitems-wrapper">'.'<div id="collitems"><ul>'.$rrr.'</ul></div>'.'</div>'
        .'<div id="expotool" class="collitem-edit-tool"><div class="wrapper">'
        .'<a href="#" class="collitem-edit"><span><i class="fa fa-pencil-square-o"></i> 編輯／移除藏品</span></a>'
        .'<a href="#" class="collitem-edit-fin"><span><i class="fa fa-check-circle-o"></i> 編輯完成</span></a>'
        .'</div></div>'
        ,
      );
  }

  private function _buildForm_public318(&$entity, &$form, &$form_state) {
    $form['public318'] =
      array(
        '#tree' => TRUE,
        '#weight' => 5,
        '#type' => 'details',
        '#title' => '從public.318.io選取資料',
        '#description' => '個人策展包含之資料需來自public.318.io，加入後，你可以為每一張藏品加入自己的說明。',
        '#open' => TRUE,
      );
    $form['public318']['searchtext'] =
      array(
        '#type' => 'textfield',
        '#default_value' => '',
      );
    $tag = WG::xmlTag('span', '<i class="fa fa-search"></i> 找', ['class'=>'public318search btn btn-primary btn-lg',]);
    $form['public318']['searchbtn'] = array ('#markup' => $tag);
    $form['public318']['wrap'] =
      array(
        '#markup' => '
        <div id="search-result-wrapper">
        <div id="search-result"></div>
        </div>
        ',
      );
    $tag = WG::xmlTag('span', '<i class="fa fa-angle-down"></i> 加入', ['class'=>'public318add btn btn-primary btn-lg',]);
    $form['public318']['add'] = array ('#markup' => '<div class="public318add-wrap">'.$tag.'</div>');
    $form['public318']['selected'] = ['#type' => 'hidden',];
  }

  private function _buildForm_base(&$entity, &$form, &$form_state) {
    $form['base'] = [
                      '#tree' => TRUE,
                      '#weight'=> -10,
                      '#type' => 'details',
                      '#title' => '基本資訊',
                      '#description' => '關於本個人策展的基本資訊。<p class="veryimportant">電子郵件信箱為必填，你的分享與編輯網址將會寄到你的電子郵件信箱！</p>',
                      '#open' => TRUE,
                    ];

    $form['base']['nid'] = ['#type' => 'hidden', '#value' => ($entity)?$entity->id():null];
    $form['base']['email'] = ['#type' => 'textfield', '#title' => '電子郵件信箱', '#required' => True, '#default_value' => ($entity)?WG::entity_get_field_value($entity, 'field_authoremail'):''];
    $form['base']['author'] = ['#type' => 'textfield', '#title' => '作者（暱稱）', '#default_value' => ($entity)?WG::entity_get_field_value($entity, 'field_author_plain'):'匿名者'];
    $form['base']['title'] = ['#type' => 'textfield', '#title' => '標題', '#default_value' => ($entity)?$entity->getTitle():'無題'];


    $featuredimage = null;
    if($entity) {
      $target_id = WG::entity_get_field_value($entity, 'field_featuredimage', 0, 'target_id');
      if($target_id) $featuredimage = [$target_id];
    }

    $form['base']['featuredimage'] =
      [
        '#title' => '代表圖片',
        '#type' => 'managed_file',
        '#description' => '上傳一張可代表本策展的圖片',
        '#upload_location' => 'public://p/',
        '#default_value' => $featuredimage,
      ];
    $form['base']['public'] =
      [
        '#type' => 'checkbox',
        '#title' => '同意您的個人策展顯示於「我們的318｜個人策展」列表中',
        '#default_value' => ($entity)?WG::entity_get_field_value($entity, 'field_public'):1,
      ];


    $form['base']['body'] =
      [
        '#type' => 'text_format',
        '#title' => t('描述'),
        '#rows' => 3,
        '#format' => 'rich_html_base',
        '#default_value' => ($entity)?WG::entity_get_field_value($entity, 'field_body'):null
      ];
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $d = [
           'nid' => $form_state->getValue(['base', 'nid']),
           'title' => $form_state->getValue(['base', 'title']),
           'email' => $form_state->getValue(['base', 'email']),
           'author' => $form_state->getValue(['base', 'author']),
           'public' => $form_state->getValue(['base', 'public']),
           'body' => $form_state->getValue(['base', 'body']),
           'showinfront' => 0,
           'hide' => 0,
           'highlight' => 0,
           //'weight' => 0
         ];
    $fi = $form_state->getValue(['base', 'featuredimage']);
    if($fi) {
      $fid = array_shift($fi);
      $d['featuredimage'] = array('target_id' => $fid,);
    }

    $collitems_json = $form_state->getValue(['collitems', 'data']);
    $collitems = Json::decode($collitems_json);
    foreach ($collitems as &$collitem) {
      $anno = $collitem['annotation'];
      if(is_array($anno)) $collitem['annotation'] = $anno;
      else $collitem['annotation'] = array('value'=>'<p>'.nl2br($anno).'</p>', 'format'=>'rich_html_base');
    }
    $d['collitems'] = $collitems;
    $is_new=($d['nid'])?false:true;

    $entity = \Drupal\expo\ExpoCrud::saveExpo($d);
    $nid = $entity->id();
    $form_state->setRedirect('entity.node.canonical', ['node'=>$nid]);
    $viewhash = WG::entity_get_field_value($entity, 'field_viewhash');
    $edithash = WG::entity_get_field_value($entity, 'field_edithash');

    //entity.node.canonical
    global $base_url;
    $viewurl = $base_url.'/p/'.$viewhash;
    $editurl = $base_url.'/p/edit/'.$edithash;

    drupal_set_message(
      t('已儲存，請使用以下連結分享與編輯你的個人策展。<br/>分享：@viewurl<br/>編輯：@editurl',
        array(
          '@viewurl' => $base_url.'/p/'.$viewhash,
          '@editurl' => $base_url.'/p/edit/'.$edithash,
        )));
    if($is_new) {
      $to = $d['email'];
      _expo_mailhash($to, $viewurl, $editurl);
      $to = 'ghtiun@wordgleaner.com';
      _expo_mailhash($to, $viewurl, $editurl);
    }

  }

  public function content_edithighlight($id, $stat) {
    $node = node_load($id);
    $node->field_highlight->setValue($stat);
    $node->save();
    $r = $id .' '.$stat;
    echo $r;
    die();
  }

  public function content_public318search() {
    $qs = trim($_GET['qs']);
    $r = '';
    if($qs == '') {
      $r .= "請輸入搜尋字（以下為隨機挑選）";
      $public318_ids = [
                         10478, 11857, 11861, 11897, 11902, 11914, 11933, 11940, 11941, 11956,
                         11960, 11962, 12377, 12378, 12382, 12395, 12578, 12663, 12669, 12681,
                         12684, 13035, 13049, 13050, 13051, 13064, 13065, 13066, 13067, 13070,
                         13076, 13077, 13078, 13100, 13121, 13122, 13130, 13135, 13136, 13138,
                         13140, 13142, 13144, 13145, 13149, 13173, 13182, 13202, 13225, 13253,
                         13255, 13258, 13259, 13260, 13264, 13265, 13266, 13267, 13292, 13305,
                         13313, 13325, 13328, 13329, 13343, 13348, 13349, 13350, 13351, 13352,
                         13353, 13354, 13356, 13377, 13391, 13394, 13395, 13396, 13413, 13416,
                         13428, 14944, 14945, 14946, 14948, 14951, 14952, 14953, 14954, 14955,
                         14956, 14957, 14959, 14960, 14961, 14962, 14963, 14967, 14968, 14969,
                         14970, 14971, 14972,
                       ];
      $cc = count($public318_ids);
      $tt = DT::random_num_list(0, $cc-1, 18);
      $ids = [];
      foreach ($tt as $tk) {
        $ids[] = $public318_ids[$tk];
      }
      $qs = implode(',', $ids);
      $select = self::_content_public318search_search($qs);
      if($select === false) {
        $r .= "找不到...";
      } else {
        $r .= $select;
      }
    } else {
      $select = self::_content_public318search_search($qs);
      if($select === false) {
        $r .= "找不到...";
      } else {
        $r .= $qs;
        $r .= $select;
      }
    }
    echo $r;
    die();
  }

  private function _content_public318search_search($qs) {
    $qs = trim($qs);
    $url = _expo_public318_api_url($qs);
    $json = WG::fetch_url($url, false);

    $ret = Json::decode($json);
    $datas = $ret['results'];
    if(count($datas) == 0) {
      $r = false;
    } else {
      $options = '';
      foreach($datas as $data) {
        $target = $data['identifier'];
        $options .= WG::xmlTag('option', '<a href="aa">'.$target.'</a>',
                               [
                                 'data-img-src' => $data['thumb'],
                                 'value' => $target,
                               ]);
      }
      $r = WG::xmlTag('select', $options,
                      [
                        'multiple' => 'multiple',
                        'class' => 'image-picker show-labels',
                        'id' => 'theitems',
                      ]);
    }

    return $r;
  }

  public function content_public318add() {
    $json = $_GET['ids'];
    $ids = Json::decode($json);
    $r = '';
    if($ids) {
      $form = ['items'=>array()];
      $i = 0;
      foreach($ids as $identifier) {
        $i++;
        $delta = microtime();
        $delta = preg_replace('%[^0-9]%', '', $delta);
        $form['items'][$delta] = ['#type'=>'container', '#prefix' => '<li><div class="collitem-element-wrapper" id="collitem-index-'.$delta.'">', '#suffix' => '</div></li>'];
        $this->_collitem_element_make($form['items'][$delta], $delta, $identifier);
      }
      $r = render($form);
    }
    echo $r;
    die();
  }

  public function content_public318edit($id) {
    $entity = node_load($id);
    $r = '';
    if($entity) {
      $form = ['items'=>array()];
      $cnt = count($entity->field_collitem);
      for($i=0; $i<$cnt; $i++) {
        $fcid = $entity->field_collitem[$i]->value;
        $fcitem = \Drupal\field_collection\Entity\FieldCollectionItem::load($fcid);
        $target = WG::entity_get_field_value($fcitem, 'field_target');
        $annotation = WG::entity_get_field_value($fcitem, 'field_annotation');
        extract(_expo_extract_collitem_target($target));
        $identifier = $id;
        //$collitems[] = ['target'=>$target, 'annotation'=>$annotation];
        $delta = microtime();
        $delta = preg_replace('%[^0-9]%', '', $delta);
        $form['items'][$delta] = ['#type'=>'container', '#prefix' => '<li><div class="collitem-element-wrapper" id="collitem-index-'.$delta.'">', '#suffix' => '</div></li>'];
        $this->_collitem_element_make2($form['items'][$delta], $delta, $identifier, $fcid, $annotation);
      }

      $r .= render($form);

    }
    echo $r;
    die();
  }

  private function _collitem_element_make2(&$el, $i, $identifier, $fcid, $annotation='', $stylename = 'expothumb') {
    $icon_info = _expo_public318_get_content_by_id($identifier, 'icon_info');
    $icon_uri = _expo_public318_get_icon_by_id($identifier, $icon_info);
    $icontag = WG::render_styled_image($icon_uri, $stylename);
    $el['icon'] =['#markup'=>'<div class="collitem-icon">'.$icontag.'</div>', '#weight' => -10];
    $el['annotationwrapper'] =
      [
        '#prefix' => '<div class="annotationwrapper">',
        '#suffix' => '</div>',
      ];
    $el['annotationwrapper']['annotation'] = array(
          '#type' => 'textarea',
          '#title' => t('我的註記'),
          '#attributes'=>['class'=>['theannotation']],
          '#value'=>strip_tags($annotation)
        );
    $el['target'] = ['#type' => 'hidden', '#value' => 'public318://'.$identifier, '#attributes'=>['class'=>['thetarget']]];

    $el['actions'] =
      [
        '#type' => 'container',
        '#prefix' => '<span class="collitem-edit-actions">',
        '#suffix'=>'</span>',
        '#weight'=>50
      ];

    $el['actions']['remove'] =
      array(
        '#markup' => '<a href="#" class="collitem-remove btn btn-default" data-index="'.$i.'">移除</i></a>',
      );


  }

  private function _collitem_element_make(&$el, $i, $identifier, $fcid, $annotation='', $stylename = 'expothumb') {
    $icon_info = _expo_public318_get_content_by_id($identifier, 'icon_info');
    $icon_uri = _expo_public318_get_icon_by_id($identifier, $icon_info);
    $icontag = WG::render_styled_image($icon_uri, $stylename);
    $el['icon'] =['#markup'=>'<div class="collitem-icon">'.$icontag.'</div>', '#weight' => -10];
    $el['annotationwrapper'] =
      [
        '#prefix' => '<div class="annotationwrapper">',
        '#suffix' => '</div>',
      ];
    $el['annotationwrapper']['annotation'] = array(
          '#type' => 'textarea',
          '#title' => t('我的註記'),
          '#attributes'=>['class'=>['theannotation']]
        );
    $el['target'] = ['#type' => 'hidden', '#value' => 'public318://'.$identifier, '#attributes'=>['class'=>['thetarget']]];

    $el['actions'] =
      [
        '#type' => 'container',
        '#prefix' => '<span class="collitem-edit-actions">',
        '#suffix'=>'</span>',
        '#weight'=>50
      ];

    $el['actions']['remove'] =
      array(
        '#markup' => '<a href="#" class="collitem-remove btn btn-default" data-index="'.$i.'">移除</i></a>',
      );


  }

}
