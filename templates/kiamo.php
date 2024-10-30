<?php
/**
 * Kiamo template
 * Please do NOT modify ! Copy this file to your theme and customize it
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$opts = kiamo_get_options();
$user = wp_get_current_user();
?>
<div id="kiamo-tab">
  <a href="javascript:void(0)" onclick="Kiamo.open()">
    <img src="<?= plugins_url('../assets/kc-ico-chat.png', __FILE__)?>" alt=""> <?= __('Got a question ?', 'kiamo') ?>
  </a>
</div>
<div id="fake-kiamo-chat">
  <div id="fake-kc-header">
    <div id="fake-kc-logo"></div>
    <div id="fake-kc-topbar-actions">
      <div class="kc-icon fake-kc-close" onclick="Kiamo.close()"></div>
    </div>
  </div>
  <div id="fake-kc-body">
    <div id="fake-kc-message-list">
      <div class="kc-choice">
        <p><?= get_theme_mod('kiamo_kiamo_text', __('Please choose chat or web call back', 'kiamo')) ?></p>
        <?php if ($opts['callback_enabled']) :?>
          <a class="btn-callback" href="javascript:void(0)" onclick="Kiamo.callbackInit()"><img src="<?= get_theme_mod('kiamo_kiamo_callback_button', plugins_url('../assets/kc-btn-telephone.png', __FILE__)) ?>"></a>
        <?php endif;?>
        <?php if ($opts['chat_enabled']) :?>
          <a class="btn-chat" href="javascript:void(0)" onclick="Kiamo.start()"><img src="<?= get_theme_mod('kiamo_kiamo_chat_button', plugins_url('../assets/kc-btn-chat.png', __FILE__)) ?>"></a>
        <?php endif;?>
      </div>
      <form onsubmit="Kiamo.callbackSubmit(this); return false;" class="kc-callback-form" style="display: none">
        <input type="hidden" name="action" value="kiamo_callback">
        <?php if (isset($opts['callback_service_id'])) :?>
          <input type="hidden" name="service_id" value="<?= $opts['callback_service_id'] ?>">
        <?php endif; ?>
        <?php if (isset($opts['callback_agent_id'])) :?>
          <input type="hidden" name="agent_id" value="<?= $opts['callback_agent_id'] ?>">
        <?php endif; ?>
        <div class="callback-container">
          <h3><?= __('Web call back', 'kiamo') ?></h3>
          <p><?= __('Please fill in this form so we can call you back', 'kiamo') ?></p>
          <p><label><?= __('Name', 'kiamo') ?> : </label><input name="name" value="<?= $user ? $user->display_name : '' ?>"></p>
          <p><label><?= __('Phone', 'kiamo') ?> : </label><input name="phone" value=""></p>
          <p><label><?= __('Call me back', 'kiamo') ?> : </label><select name="timestamp">
            <option value=""><?= __('As soon as possible', 'kiamo') ?></option>
            <option value="07:30"><?= __('From 7 am', 'kiamo') ?></option>
            <option value="10:00"><?= __('From 10 am', 'kiamo') ?></option>
            <option value="12:00"><?= __('From 12 am', 'kiamo') ?></option>
            <option value="14:00"><?= __('From 2 pm', 'kiamo') ?></option>
            <option value="16:00"><?= __('From 4 pm', 'kiamo') ?></option>
          </select>
          <input class="btn" type="submit" value="OK">
          </p>
        </div>
      </form>
    </div>
  </div>
</div>
