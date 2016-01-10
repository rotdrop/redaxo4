<?php

$cafevdb = new cafevdb();

$url      = rex_post('url', 'string', $cafevdb->url);
$user     = rex_post('user', 'string', $cafevdb->user);
$password = rex_post('password', 'string', $cafevdb->password);

$message = '';

if (rex_post('btn_save', 'string') !== '') {
  $file = rex_path::addonData('cafevdb', 'settings.inc.php');

  $message  = $I18N->msg('cafevdb_config_saved_error');

  // for marginal larger security we encrypt the password with the REX['INSTNAME']
  $content = '<?php
$this->url = $this->decrypt(' . var_export($cafevdb->encrypt($url), true) . ');
$this->user = $this->decrypt(' . var_export($cafevdb->encrypt($user), true) . ');
$this->password = $this->decrypt(' . var_export($cafevdb->encrypt($password), true) . ');
';

  if (filter_var($url, FILTER_VALIDATE_URL) === false) {
    $message  = $I18N->msg('cafevdb_url_invalid_error');
  } else if (rex_file::put($file, $content) !== false) {
    $message = $I18N->msg('cafevdb_config_saved_successful');
  }
}

if ($message != '') {
    echo rex_info($message);
}

?>


<div class="rex-addon-output">
  <h2 class="rex-hl2"><?php echo $I18N->msg('cafevdb_config_settings'); ?></h2>

  <div id="rex-addon-editmode" class="rex-form">

    <form action="" method="post">

      <fieldset class="rex-form-col-1">

        <div class="rex-form-wrapper">

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-text">
              <label for="url"><?php echo $I18N->msg('cafevdb_url'); ?></label>
              <input type="text" name="url" id="url" value="<?php echo $url ?>" />
            </p>
          </div>

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-text">
              <label for="user"><?php echo $I18N->msg('cafevdb_user'); ?></label>
              <input type="text" name="user" id="user" value="<?php echo $user ?>" />
            </p>
          </div>

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-text">
              <label for="password"><?php echo $I18N->msg('cafevdb_password'); ?></label>
              <input type="text" name="password" id="password" value="<?php echo $password ?>" />
            </p>
          </div>

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-submit">
              <input class="rex-form-submit" type="submit" name="btn_save" value="<?php echo $I18N->msg('cafevdb_save'); ?>" />
              <input class="rex-form-submit rex-form-submit-2" type="reset" name="btn_reset" value="<?php echo $I18N->msg('cafevdb_reset'); ?>" onclick="return confirm('<?php echo $I18N->msg('cafevdb_reset_info'); ?>');"/>
            </p>
          </div>

        </div>

      </fieldset>

    </form>
  </div>
</div>
