<form action="<?=$_SERVER['REQUEST_URI']?>" method="post" class="form-login">
	<div class="item-text">
		<h1 class="marginalie">Dieser Bereich ist nur für Mitglieder</h1>
		<p><strong>Bitte geben Sie Ihren Usernamen und Passwort ein</strong></p>
		<fieldset>
		<label for="username">Benutzername</label><input type="text" id="username" name="username" maxlength="254" class="login" />
		<label for="password">Passwort</label><input type="text" id="password" name="password" maxlength="254" class="login" />
		<button type="submit" name="cmd" value="login">Einloggen</button>
		</fieldset>
	</div>
</form>