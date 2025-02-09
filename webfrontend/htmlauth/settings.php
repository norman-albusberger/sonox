<?php
require_once "loxberry_system.php";
require_once "loxberry_io.php";
require_once "loxberry_web.php";
require_once "Sonox.php";
$sonox = new Sonox();
$mqttDetails = mqtt_connectiondetails();

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
$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $port = $_POST['port'];
    $mqttActive = $_POST['mqttActive']==='true';

    // Bestehende Einstellungen laden
    $existingSettings = $sonox->readSettings();

    // Einstellungen speichern
    $sonox->saveSettings([
        'port' => $port,
        'mqtt' => ['active' => $mqttActive,
            'broker' => $mqttDetails['brokeraddress'],
            'username' => $mqttDetails['brokeruser'],
            'password' => $mqttDetails['brokerpass'],
        ]
    ]);

    // Erfolgsmeldung setzen
    $successMessage = "{$L['COMMON.SAVED']}";

    // Service neu starten
    $result = $sonox->restartService();

    if ($result['success']) {
        $successMessage .= "<br>{$L['SETTINGS.RESTART_SUCCESS']}";
    } else {
        $errorMessage = "{$L['SETTINGS.RESTART_ERROR']}<br>" . implode("<br>", $result['output']);
    }
}

$settings = $sonox->readSettings();
?>

<!-- 📌 Erfolgsmeldung (wird nach 3 Sekunden ausgeblendet) -->
<?php if (!empty($successMessage)): ?>
    <div id="message-box" class="ui-bar ui-bar-b" role="alert">
        <?= $successMessage ?>
    </div>
<?php endif; ?>

<?php if (!empty($errorMessage)): ?>
    <div id="error-box" class="ui-bar ui-bar-r" role="alert">
        <?= $errorMessage ?>
    </div>
<?php endif; ?>

<script>
    setTimeout(function () {
        $("#message-box, #error-box").fadeOut();
    }, 3000); // Nach 3 Sekunden ausblenden
</script>

<!-- 📌 Formular -->
<form method="POST">
    <h2><?= $L['COMMON.SETTINGS'] ?></h2>

    <div class="ui-field-contain">
        <label for="port"><?= $L['SETTINGS.SERVER_PORT'] ?></label>
        <input type="number" id="port" name="port"
               value="<?= htmlspecialchars($settings['port'] ?? 5005) ?>" required>
    </div>

    <div class="ui-field-contain">
        <label for="mqttActive"><?= $L['SETTINGS.MQTT_ACTIVE'] ?></label>
        <select name="mqttActive" id="mqttActive" data-role="flipswitch">
            <option value="false" <?= !$settings['mqtt']['active'] ? 'selected' : '' ?>><?= $L['COMMON.NO'] ?></option>
            <option value="true" <?= $settings['mqtt']['active'] ? 'selected' : '' ?>><?= $L['COMMON.YES'] ?></option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary"><?= $L['SETTINGS.SAVE'] ?></button>
</form>

<hr>

<?php
// Footer ausgeben
LBWeb::lbfooter();
?>
