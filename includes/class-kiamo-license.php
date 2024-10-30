<?php
/**
 * Kiamo_License
 *
 * @since      1.0.0
 * @package    Kiamo
 * @author     Service technique IRCF <technique@ircf.fr>


/*
 * Kiamo_License
 */

class Kiamo_License {

    static private $instance;

    static function getInstance(){
      if (!isset(self::$instance)){
        self::$instance = new self;
      }
      return self::$instance;
    }

    /**
    * Admin tools
    */
    public function admin_options() {
        echo '<h3>' . __( 'Register your license', 'kiamo') . '</h3>';

        if (get_option('kiamo_license_key') == false){
            echo '<p>' . __( 'You currently use the free version, <a href="https://ircf.fr/plugins-wordpress/" target="_blank">click here to purchase the pro version</a>.' , 'wcmpg' ) . '</p>';
            echo '
              <form method="POST" action="#">
              <label for="url">'.__( 'Register your license', 'kiamo').'</label>
              <input id="url" name="kiamo_license_key" type="text" size="50">
              <input type="submit" value="'.__( 'Validate', 'kiamo').'" class="button-primary">
              </form><br>
            ';
        }
        else{
            echo '<p>' . __( 'You currently use the pro version, <a  href="https://ircf.fr/plugins-wordpress/" target="_blank">click here to get more information</a>.' , 'wcmpg' ) . '</p>';
        }
        echo '<p>' . __( 'You can contact our technical support by phone at <a href="tel:+33553467179">+33 5 53 46 71 79</a> or by e-mail at <a href="mailto:technique@ircf.fr">technique@ircf.fr</a>', 'wcmpg') . '</p>';

        if (isset($this->error_message)){
          echo '<div class="notice notice-warning"> <p><strong>'.$this->error_message.'</strong></p></div>';
        }
        if (isset($this->success_message)){
          echo '<div class="notice notice-success"> <p><strong>'.$this->success_message.'</strong></p></div>';
        }
    }
    /**
     * Process admin options
     */
    public function process_admin_options(){

      if (isset($_POST['kiamo_license_key'])){
        $kiamo_license_key =  sanitize_text_field($_POST['kiamo_license_key']);
        $this->verify_license($kiamo_license_key);
      }
    }

    /**
     * Check if the license filled in is correct or not
     */
  function verify_license($kiamo_license){
      $api_params = array(
      'slm_action'        => 'slm_activate',
      'secret_key'        => '623p9e73mas6825twqgy',
      'license_key'       => $kiamo_license,
      'registered_domain' => $_SERVER['SERVER_NAME'],
      'item_reference'    => urlencode('Kiamo'),
    );
    $query = esc_url_raw(add_query_arg($api_params, 'https://ircf.fr'));
    $response = wp_remote_get($query, array('timeout' => 15, 'sslverify' => false));
    if (is_wp_error($response)){
        $this->error_message = __('Unexpected Error! The query returned with an error.' , 'kiamo' );
    }

    $license_data = json_decode(wp_remote_retrieve_body($response));
    if($license_data->result == 'success'){
      $this->success_message = $license_data->message;
      if (get_option('kiamo_license_key') == false){
        add_option( 'kiamo_license_key', $kiamo_license );
      }else{
        update_option( 'kiamo_license_key', $kiamo_license );
      }
    } else {
      $this->error_message = $license_data->message;
    }
  }
}
