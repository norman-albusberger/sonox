<?php
require_once "loxberry_web.php";
require_once "loxberry_log.php";
require_once "Sonox.php";
$sonox = new Sonox();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sprachdateien laden
$L = LBSystem::readlanguage("language.ini");

// Titel und Navigation
require_once "navigation.php";
$navbar[2]['active'] = true;

// Header ausgeben
LBWeb::lbheader($L['PLUGIN.TITLE'], "http://www.loxwiki.eu:80/x/2wzL", "help.html");

// Verarbeitung von Formulardaten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $port = $_POST['port'];

    // Bestehende Einstellungen laden
    $existingSettings = $sonox->readSettings();
    // Einstellungen speichern
    $sonox->saveSettings([
        'port' => $port,
    ]);

    echo "<div class='alert alert-success'>{$L['COMMON.SAVED']}</div>";

    $result = $sonox->restartService();

    if ($result['success']) {
        echo $L['SETTINGS.RESTART_SUCCESS'];
    } else {
        echo $L['SETTINGS.RESTART_ERROR'] . implode("\n", $result['output']);
    }

    //load settings from file


}
$settings = $sonox->readSettings();

// Formular anzeigen
?>
<form method="POST">
    <h2><?= $L['COMMON.SETTINGS'] ?></h2>


    <div data-role="fieldcontain">
        <label for="server_port"><?= $L['SETTINGS.SERVER_PORT'] ?></label>
        <input type="number" id="port" name="port"
               value="<?= htmlspecialchars($settings['port'] ?? 5005) ?>" required>
    </div>


    <button type="submit" class="btn btn-primary"><?= $L['SETTINGS.SAVE'] ?></button>
</form>

<hr>


<?php
// Footer ausgeben
LBWeb::lbfooter();
?>
