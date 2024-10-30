<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://ircf.fr/plugins-wordpress/
 * @since             1.0.0
 * @package           WCMPG
 *
 * @wordpress-plugin
 * Plugin Name:       Kiamo Chat and web call back by IRCF
 * Plugin URI:        https://ircf.fr/plugins-wordpress/
 * Description:       Integrates Kiamo chat and web call back on your website.
 * Version:           1.1
 * Author:            IRCF
 * Author URI:        https://ircf.fr/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       kiamo
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Enable debug mode (should be false in production)

$kiamo_debug = false;

// Default plugin options, may be modified in the Settings menu

$kiamo_default_options = array(
);

// Initialize plugin

require_once( plugin_dir_path( __FILE__ ).'includes/class-kiamo-license.php' );

function kiamo_loaded() {
  load_plugin_textdomain( 'kiamo', FALSE, 'kiamo/languages' ); // Required for Wordpress < 4.6
}
add_action('plugins_loaded', 'kiamo_loaded', 0);

// Kiamo admin panel

add_action('admin_menu', 'kiamo_menu');

function kiamo_menu() {
  add_options_page('Kiamo', 'Kiamo', 'activate_plugins', 'kiamo', 'kiamo_options');	 
}

function kiamo_get_options($field=null){
  global $kiamo_default_options;
  $options = get_option('kiamo_options');
  if (!is_array($options)) $options = array();
  $options = array_merge($kiamo_default_options, $options);
  if (isset($field)){
    return $options[$field];
  }else{
    return $options;
  }
}

function kiamo_enabled(){
  $opts = kiamo_get_options();
  return $opts['chat_enabled'] && !empty($opts['chat_server'])
    || $opts['callback_enabled'] && !empty($opts['callback_server']);
}

function kiamo_options() {
  global $kiamo_default_options;
  $options = kiamo_get_options();
  $license = Kiamo_License::getInstance();
  if (!is_admin()){
    ?><div class="error notice"><p><?=__('Sorry, only admin can edit kiamo options', 'kiamo' )?></p></div><?php
    return;
  }
  if (isset($_POST['Submit']) && check_admin_referer('kiamo_options')){
    if ($_POST["Submit"] == __('Update', 'kiamo' )){
      if (!isset($_POST['kiamo_chat_enabled'])) $_POST['kiamo_chat_enabled'] = 0;
      if (!isset($_POST['kiamo_callback_enabled'])) $_POST['kiamo_callback_enabled'] = 0;
      foreach($_POST as $key => $value){
        if (substr($key, 0, 6) == 'kiamo_'){
          $options[str_replace('kiamo_', '', sanitize_key($key))] = sanitize_text_field($value);
        }
      }
      update_option('kiamo_options', $options);
      echo '<div class="updated notice"><p><strong>'.__('Options saved', 'kiamo' ).'</strong></p></div>';
    }elseif ($_POST["Submit"] == __('Reset', 'kiamo' )){
      update_option('kiamo_options', $kiamo_default_options);
      echo '<div class="updated notice"><p><strong>'.__('Options resetted', 'kiamo' ).'</strong></p></div>';
    }
  }
  $license->process_admin_options();
  $appearance_url = add_query_arg( array('autofocus[section]' => 'kiamo_kiamooptions'), admin_url( 'customize.php' ) );
  ?>
  <div class="wrap">
    <style>
      form.options label.control-label{
        display: inline-block;
        width: 250px;
      }
      form.options div.controls{
        display: inline-block;
      }
    </style>
    <script>
      var kiamo_options_chat_enable = function(){
        var enabled = jQuery('input[name="kiamo_chat_enabled"]:checked').length == 1;
        kiamo_options_chat_mode();
        jQuery('.form-group-chat').toggle(enabled);
      };
      var kiamo_options_callback_enable = function(){
        var enabled = jQuery('input[name="kiamo_callback_enabled"]:checked').length == 1;
        jQuery('.form-group-callback').toggle(enabled);
      };
      var kiamo_options_chat_mode = function(){
        jQuery('.form-group-chat-mode').hide();
        if (jQuery('input[name="kiamo_chat_enabled"]:checked').length <= 0) return;
        if (jQuery('input[name="kiamo_chat_mode"]:checked').length <= 0) return;
        var mode = jQuery('input[name="kiamo_chat_mode"]:checked').val();
        jQuery('.form-group-chat-mode-' + mode).show();
      };
      jQuery(window).ready(function(){
        jQuery('input[name="kiamo_chat_enabled"]').click(kiamo_options_chat_enable);
        jQuery('input[name="kiamo_callback_enabled"]').click(kiamo_options_callback_enable);
        jQuery('input[name="kiamo_chat_mode"]').click(kiamo_options_chat_mode);
        kiamo_options_chat_enable();
        kiamo_options_callback_enable();
        kiamo_options_chat_mode();
      });
    </script>
    <form method="post" class="options" target="_self">
      <?php wp_nonce_field( 'kiamo_options' ); ?>
      <h2><?=__('Configure Kiamo', 'kiamo' )?></h2>
      <p><?=__('Kiamo plugin by IRCF is a non-official WordPress plugin for Kiamo chat and call back services. The author of this plugin (IRCF) is not related in any way to Kiamo brand.', 'kiamo' )?></p>
      <p><?=__('For more information about Kiamo, please visit <a href="https://kiamo.fr" target="_blank">the official Kiamo website</a>', 'kiamo' )?></p>
      <?php if (get_option('kiamo_license_key') == false) :?>
        <p><?=__('Kiamo appearance settings are only available on pro version', 'kiamo' )?></p>
      <?php else :?>
        <p><a href="<?php echo esc_url( $appearance_url ); ?>"><?=__('Edit Kiamo appearance settings', 'kiamo')?></a></p>
      <?php endif;?>
      <h3><?=__('Kiamo chat', 'kiamo' )?></h3>
      <div class="form-group">
        <label class="control-label"><?=__('Enable chat', 'kiamo' )?></label>
        <div class="controls"><input type="checkbox" name="kiamo_chat_enabled" value="1" <?=@$options['chat_enabled'] == 1 ? 'checked' : ''?>></div>
      </div>
      <div class="form-group form-group-chat">
        <label class="control-label"><?=__('Server name or IP', 'kiamo' )?></label>
        <div class="controls"><input type="text" name="kiamo_chat_server" value="<?=@$options['chat_server']?>" size="50"></div>
      </div>
      <div class="form-group form-group-chat">
        <label class="control-label"><?=__('Mode', 'kiamo' )?></label>
        <div class="controls">
          <label><input type="radio" name="kiamo_chat_mode" value="chat" <?= @$options['chat_mode'] == 'chat' ? 'checked' : '' ?>><?=__('Chat', 'kiamo' )?></label>
          <label><input type="radio" name="kiamo_chat_mode" value="targeting" <?= @$options['chat_mode'] == 'targeting' ? 'checked' : '' ?>><?=__('Targeting', 'kiamo' )?></label>
        </div>
      </div>
      <div class="form-group form-group-chat form-group-chat-mode form-group-chat-mode-chat">
        <label class="control-label"><?=__('Chat ID', 'kiamo' )?></label>
        <div class="controls"><input type="text" name="kiamo_chat_id" value="<?=@$options['chat_id']?>" size="50"></div>
      </div>
      <div class="form-group form-group-chat form-group-chat-mode form-group-chat-mode-targeting">
        <label class="control-label"><?=__('Targeting ID', 'kiamo' )?></label>
        <div class="controls"><input type="text" name="kiamo_chat_targeting_id" value="<?=@$options['chat_targeting_id']?>" size="50"></div>
      </div>
      <div class="form-group form-group-chat">
        <label class="control-label"><?=__('Agent ID', 'kiamo' )?></label>
        <div class="controls"><input type="text" name="kiamo_chat_agent_id" value="<?=@$options['chat_agent_id']?>" size="4"></div>
      </div>
      <div class="form-group form-group-chat">
        <label class="control-label"><?=__('Customer ID', 'kiamo' )?></label>
        <div class="controls"><input type="text" name="kiamo_chat_customer_id" value="<?=@$options['chat_customer_id']?>" size="4"></div>
      </div>
      <h3><?=__('Kiamo call back', 'kiamo' )?></h3>
      <?php if (get_option('kiamo_license_key') == false) :?>
        <?=__('Kiamo call back is only available on pro version', 'kiamo' )?>
      <?php else :?>
        <div class="form-group">
          <label class="control-label"><?=__('Enable call back', 'kiamo' )?></label>
          <div class="controls"><input type="checkbox" name="kiamo_callback_enabled" value="1" <?=@$options['callback_enabled'] == 1 ? 'checked' : ''?>></div>
        </div>
        <div class="form-group form-group-callback">
          <label class="control-label"><?=__('Server name or IP', 'kiamo' )?></label>
          <div class="controls"><input type="text" name="kiamo_callback_server" value="<?=@$options['callback_server']?>" size="50"></div>
        </div>
        <div class="form-group form-group-callback">
          <label class="control-label"><?=__('Token', 'kiamo' )?></label>
          <div class="controls"><input type="text" name="kiamo_callback_token" value="<?=@$options['callback_token']?>" size="50"></div>
        </div>
        <div class="form-group form-group-callback">
          <label class="control-label"><?=__('Service ID', 'kiamo' )?></label>
          <div class="controls"><input type="text" name="kiamo_callback_service_id" value="<?=@$options['callback_service_id']?>" size="4"></div>
        </div>
        <div class="form-group form-group-callback">
          <label class="control-label"><?=__('Agent ID', 'kiamo' )?></label>
          <div class="controls"><input type="text" name="kiamo_callback_agent_id" value="<?=@$options['callback_agent_id']?>" size="4"></div>
        </div>
        <div class="form-group form-group-callback">
          <label class="control-label"><?=__('Customer ID', 'kiamo' )?></label>
          <div class="controls"><input type="text" name="kiamo_callback_customer_id" value="<?=@$options['callback_customer_id']?>" size="4"></div>
        </div>
      <?php endif; ?>
      <p class="submit">
        <input type="submit" name="Submit" value="<?=__('Update', 'kiamo' )?>" class="button-primary" />
        <input type="submit" name="Submit" value="<?=__('Reset', 'kiamo' )?>" class="button" />
      </p>
    </form>
    <?= $license->admin_options() ?>
  </div>
  <?php
}

// Kiamo customizer

class Kiamo_Customizer {

  public static function sections(){
    return array(
      'kiamo' => array(
        'title' => 'Kiamo',
        'priority' => 10,
        'description' => __('Kiamo appearance settings', 'kiamo'),
        'fields' => array(
          'logo' => array(
            'class' => 'WP_Customize_Image_Control',
            'control' => array(
              'label' => __('Logo', 'kiamo'),
            ),
            'dom' => '[id$="kiamo-chat"] [id$="kc-header"] [id$="kc-logo"]',
            'property' => 'background-image',
            'prefix' => 'url(',
            'postfix' => ') !important',
          ),
          'background_image' => array(
            'class' => 'WP_Customize_Image_Control',
            'control' => array(
              'label' => __('Background image', 'kiamo'),
            ),
            'dom' => '[id$="kiamo-chat"] [id$="kc-body"] [id$="kc-message-list"] .kc-choice',
            'property' => 'background-image',
            'prefix' => 'url(',
            'postfix' => ') !important',
          ),
          'callback_button' => array(
            'class' => 'WP_Customize_Image_Control',
            'control' => array(
              'label' => __('Callback button', 'kiamo'),
            )
          ),
          'chat_button' => array(
            'class' => 'WP_Customize_Image_Control',
            'control' => array(
              'label' => __('Chat button', 'kiamo'),
            )
          ),
          'text' => array(
            'class' => 'WP_Customize_Control',
            'control' => array(
              'label' => __('Text', 'kiamo'),
              'type' => 'text',
            )
          ),
          'background_color' => array(
            'class' => 'WP_Customize_Color_Control',
            'control' => array(
                'label' => __('Background color', 'kiamo'),
            ),
            'dom' => '#kiamo-tab, [id$="kiamo-chat"] [id$="kc-header"]',
            'property' => 'background-color',
            'postfix' => ' !important',
          ),
          'border_color' => array(
            'class' => 'WP_Customize_Color_Control',
            'control' => array(
                'label' => __('Border color', 'kiamo'),
            ),
            'dom' => '[id$="kiamo-chat"]',
            'property' => 'border-color',
            'postfix' => ' !important',
          ),
          // TODO
          /*'background_gradient_color_1' => array(
            'class' => 'WP_Customize_Color_Control',
            'control' => array(
                'label' => __('Background gradient color 1', 'kiamo'),
            ),
            'dom' => '#kiamo-tab',
            'property' => 'background-color'
          ),
          'background_gradient_color_2' => array(
            'class' => 'WP_Customize_Color_Control',
            'control' => array(
                'label' => __('Background gradient color 2', 'kiamo'),
            ),
            'dom' => '#kiamo-tab',
            'property' => 'background-color'
          ),*/
        )
      )
    );
  }

  public static function register($wp_customize) {
    foreach (self::sections() as $key_section => $section) {
      $section_name = 'kiamo_' . $key_section . 'options';
      $wp_customize->add_section($section_name, array(
        'title' => $section['title'],
        'priority' => $section['priority'],
        'description' => $section['description'],
      ));
      foreach ($section['fields'] as $key_fields => $field) {
        $settings_name = 'kiamo_' . $key_section . '_' . $key_fields;
        if (!isset($field['settings']) || empty($field['settings'])) {
          $settings = array(
            'default' => '',
            'transport' => 'refresh',
          );
        } else {
          $settings = $field['settings'];
        }
        $wp_customize->add_setting($settings_name, $settings);
        $control = array_merge($field['control'], array('section' => $section_name, 'settings' => $settings_name));
        $wp_customize->add_control(new $field['class']($wp_customize, $settings_name, $control));
      }
    }
  }

  public static function header_output() {
    $styles = '<style type="text/css">';
    foreach (self::sections() as $key_section => $section) {
      foreach ($section['fields'] as $key_fields => $field) {
        $settings_name = 'kiamo_' . $key_section . '_' . $key_fields;
        if (isset($field['dom']) && !empty($field['dom'])) {
          $prefix = isset($field['prefix']) ? $field['prefix'] : '';
          $postfix = isset($field['postfix']) ? $field['postfix'] : '';
          $styles .= self::generate_css($field['dom'], $field['property'], $settings_name, $prefix, $postfix);
        }
      }
    }
    $styles .= '</style>';
    echo($styles);
  }

  private static function generate_css($selector, $style, $mod_name, $prefix = '', $postfix = '') {
    $return = '';
    $mod = get_theme_mod($mod_name);
    if (!empty($mod)) {
      $return = sprintf('%s { %s: %s; }', $selector, $style, $prefix . $mod . $postfix);
    }
    return $return;
  }
}
if (get_option('kiamo_license_key') != false){
  add_action('customize_register', array('Kiamo_Customizer', 'register'));
  add_action('wp_head', array('Kiamo_Customizer', 'header_output'));
}

// Kiamo chat

function kiamo_enqueue_scripts(){
  if (!kiamo_enabled()) return;
  $opts = kiamo_get_options();
  if (strpos($opts['chat_server'], 'http') !== 0){
    $opts['chat_server'] = '//' . $opts['chat_server'];
  }
  if ($opts['chat_mode'] == 'targeting'){
    wp_enqueue_script('kiamo_targeting', $opts['chat_server'].'/js/kiamo-targeting-'.$opts['chat_targeting_id'].'.min.js');
  }else if ($opts['chat_mode'] == 'chat'){
    wp_enqueue_script('kiamo_chat', $opts['chat_server'].'/js/kiamo-chat-'.$opts['chat_id'].'.min.js');
  }
  wp_enqueue_style('kiamo_css', plugin_dir_url( __FILE__ ) . 'assets/kiamo.css?20200618');
  wp_enqueue_script('jquery_cookie', plugin_dir_url( __FILE__ ) . 'assets/jquery.cookie.min.js', array('jquery'));
  wp_enqueue_script('kiamo_js', plugin_dir_url( __FILE__ ) . 'assets/kiamo.js', array('jquery', 'jquery_cookie'));
  wp_localize_script( 'kiamo_js', 'kiamo_options', array(
    'chat_id' => $opts['chat_id'],
    'callback_url' => admin_url( 'admin-ajax.php' ),
  ));
}
add_action( 'wp_enqueue_scripts', 'kiamo_enqueue_scripts');

function kiamo_footer(){
  if (!kiamo_enabled()) return;
  $template_file = get_template_directory() . '/kiamo.php';
  if (!file_exists($template_file)) $template_file = 'templates/kiamo.php';
  include($template_file);
}
add_action( 'wp_footer' , 'kiamo_footer' );

// Kiamo call back

function kiamo_callback_ajax(){
  try{
    if (!kiamo_enabled()) return;
    $opts = kiamo_get_options();
    if (!isset($opts['callback_service_id'])) throw new Exception(__('callback_service_id required', 'kiamo'));
    if (!isset($opts['callback_agent_id'])) throw new Exception(__('callback_agent_id required', 'kiamo'));
    if (!isset($opts['callback_customer_id'])) throw new Exception(__('callback_customer_id required', 'kiamo'));
    
    $phone = sanitize_text_field(@$_REQUEST['phone']);
    if (empty($phone)) throw new Exception(__('phone required', 'kiamo'));
    $phone = str_replace(' ', '', trim($phone));
    if (!preg_match('/^[0-9]{10}$/', $phone)) throw new Exception(__('invalid phone', 'kiamo'));
    
    $name  = sanitize_text_field(@$_REQUEST['name']);
    if (empty($name)) throw new Exception(__('name required', 'kiamo'));
    
    $timestamp  = sanitize_text_field(@$_REQUEST['timestamp']);
    
    kiamo_callback_task(array(
      'service_id' => $opts['callback_service_id'],
      'agent_id' => $opts['callback_agent_id'],
      'customer_id' => $opts['callback_customer_id'],
      'name' => $name,
      'phone' => $phone,
      'timestamp' => kiamo_callback_timestamp($timestamp),
    ));
    
    wp_send_json(array('success' => true, 'data' => __('Your callback request has been sent', 'kiamo')));
  }catch(Exception $e){
    wp_send_json_error($e->getMessage());
  }
}
if (get_option('kiamo_license_key') != false){
  add_action( 'wp_ajax_kiamo_callback', 'kiamo_callback_ajax' );
  add_action( 'wp_ajax_nopriv_kiamo_callback', 'kiamo_callback_ajax' );
}

function kiamo_callback_task($params = array()){
  global $kiamo_debug;
  $opts = kiamo_get_options();
  if (!isset($params['service_id'])) throw new Exception('service_id required');
  $uri = 'api/services/'.$params['service_id'].'/tasks';
  if (strpos($opts['callback_server'], 'http') !== 0){
    $opts['callback_server'] = 'http://' . $opts['callback_server'];
  }
  $url = $opts['callback_server'] . '/' . $uri . '?token=' . $opts['callback_token'];
  $xml = kiamo_callback_xml($params);
  if ($kiamo_debug) error_log("DEBUG kiamo_callback_task : url = $url");
  if ($kiamo_debug) error_log("DEBUG kiamo_callback_task : xml = $xml");
  $response = wp_remote_post($url, array(
    'body' => $xml,
    'timeout' => '5',
    'redirection' => '5',
    'httpversion' => '1.0',
    'blocking' => true,
    'headers' => array(),
    'cookies' => array()
  ));
  if (is_wp_error($response)){
    error_log($response->get_error_message());
    throw new Exception(__('Kiamo call back error', 'kiamo'));
  }
  if ($kiamo_debug) error_log("DEBUG kiamo_callback_task : response = " . print_r($response, true));
  if (
    !is_array($response)
    || !isset($response['body'])
    || stripos($response['body'], 'success') === false
  ){
    error_log(print_r($response, true));
    throw new Exception(__('Kiamo call back error', 'kiamo'));
  }
  return true;
}

function kiamo_callback_timestamp($timestamp){
  if (empty($timestamp)) $timestamp = date("H:i") . '+1 minute';
  $timestamp = strtotime(date("Y/m/d") . ' ' . $timestamp);
  // If time > 17h30 then day + 1 at 07h30
  if (date('H', $timestamp) > 17 || date('H', $timestamp) == 17 && date('i', $timestamp) > 30){
    $timestamp = strtotime('+1 day', $timestamp);
    $timestamp = strtotime(date('Y/m/d', $timestamp) . ' 07:30');
  }
  // If time < 7h30 then day + 0 at 07h30
  if (date('H', $timestamp) < 7 || date('H', $timestamp) == 7 && date('i', $timestamp) < 30){
    $timestamp = strtotime(date('Y/m/d', $timestamp) . ' 07:30');
  }
  // If time past then day + 1
  if ($timestamp < time()) $timestamp = strtotime('+1 day', $timestamp);
  // If week-end then next monday
  if (date('w', $timestamp) == 0) $timestamp = strtotime('+1 day', $timetamp);
  if (date('w', $timestamp) == 6) $timestamp = strtotime('+2 day', $timetamp);
  return date('d/m/Y H:i', $timestamp);
}

function kiamo_callback_xml($params = array()){
  return '<?xml version="1.0" encoding="UTF-8"?>'.
    '<tasks>'.
      '<task>'.
        '<agent id="'.$params['agent_id'].'" />'.
        '<reference>'.$params['customer_id'].'</reference>'.
        '<destination>'.$params['phone'].'</destination>'.
        '<userdata>'.
          '<CCKInteractionLabel>'.$params['name'].'</CCKInteractionLabel>'.
          '<U_DateHeure_Insertion>'.$params['timestamp'].'</U_DateHeure_Insertion>'.
        '</userdata>'.
      '</task>'.
    '</tasks>';
}
