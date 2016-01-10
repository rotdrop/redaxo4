<?php

$cafev = new cafev();

$url      = rex_post('url', 'string', $cafev->url);
$user     = rex_post('user', 'string', $cafev->user);
$password = rex_post('password', 'string', $cafev->password);

$message = '';

if (rex_post('btn_save', 'string') !== '') {
  $file = rex_path::addonData('cafev', 'settings.inc.php');

  $message  = $I18N->msg('cafev_config_saved_error');

  // for marginal larger security we encrypt the password with the REX['INSTNAME']
  $content = '<?php
$this->url = $this->decrypt(' . var_export($cafev->encrypt($url), true) . ');
$this->user = $this->decrypt(' . var_export($cafev->encrypt($user), true) . ');
$this->password = $this->decrypt(' . var_export($cafev->encrypt($password), true) . ');
';

  if (filter_var($url, FILTER_VALIDATE_URL) === false) {
    $message  = $I18N->msg('cafev_url_invalid_error');
  } else if (rex_file::put($file, $content) !== false) {
    $message = $I18N->msg('cafev_config_saved_successful');
  }
}

if ($message != '') {
    echo rex_info($message);
}

?>


<div class="rex-addon-output">
  <h2 class="rex-hl2"><?php echo $I18N->msg('cafev_config_settings'); ?></h2>

  <div id="rex-addon-editmode" class="rex-form">

    <form action="" method="post">

      <fieldset class="rex-form-col-1">

        <div class="rex-form-wrapper">

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-text">
              <label for="url"><?php echo $I18N->msg('cafev_url'); ?></label>
              <input type="text" name="url" id="url" value="<?php echo $url ?>" />
            </p>
          </div>

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-text">
              <label for="user"><?php echo $I18N->msg('cafev_user'); ?></label>
              <input type="text" name="user" id="user" value="<?php echo $user ?>" />
            </p>
          </div>

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-text">
              <label for="password"><?php echo $I18N->msg('cafev_password'); ?></label>
              <input type="text" name="password" id="password" value="<?php echo $password ?>" />
            </p>
          </div>

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-submit">
              <input class="rex-form-submit" type="submit" name="btn_save" value="<?php echo $I18N->msg('cafev_save'); ?>" />
              <input class="rex-form-submit rex-form-submit-2" type="reset" name="btn_reset" value="<?php echo $I18N->msg('cafev_reset'); ?>" onclick="return confirm('<?php echo $I18N->msg('cafev_reset_info'); ?>');"/>
            </p>
          </div>

        </div>

      </fieldset>

    </form>
  </div>
</div>
