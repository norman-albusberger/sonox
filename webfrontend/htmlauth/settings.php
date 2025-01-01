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
    $tts_provider = $_POST['tts_provider'];
    $api_key = $_POST['api_key'] ?? null;
    $region = $_POST['region'] ?? null;

    // Bestehende Einstellungen laden
    $existingSettings = $sonox->readSettings();
    // Einstellungen speichern
    $sonox->saveSettings([
        'port' => $port,
        'tts_provider' => $tts_provider,
        'api_key' => $api_key,
        'region' => $region
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


    <div class="form-group">
        <label for="server_port"><?= $L['SETTINGS.SERVER_PORT'] ?></label>
        <input type="number" id="port" name="port" class="form-control"
               value="<?= htmlspecialchars($settings['port'] ?? 5005) ?>" required>
    </div>

    <div class="form-group">
        <label for="tts_provider"><?= $L['SETTINGS.TTS_PROVIDER'] ?></label>
        <select id="tts_provider" name="tts_provider" class="form-control" required>
            <option value="google" <?= ($settings['tts_provider'] ?? '') === 'google' ? 'selected' : '' ?>>Google
            </option>
            <option value="voicerss" <?= ($settings['tts_provider'] ?? '') === 'voicerss' ? 'selected' : '' ?>>
                VoiceRSS
            </option>
            <option value="azure" <?= ($settings['tts_provider'] ?? '') === 'azure' ? 'selected' : '' ?>>Microsoft
                Azure
            </option>
            <option value="polly" <?= ($settings['tts_provider'] ?? '') === 'polly' ? 'selected' : '' ?>>AWS Polly
            </option>
        </select>
    </div>

    <div class="form-group">
        <label for="api_key"><?= $L['SETTINGS.API_KEY'] ?></label>
        <input type="text" id="api_key" name="api_key" class="form-control"
               value="<?= htmlspecialchars($settings['api_key'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label for="region"><?= $L['SETTINGS.REGION'] ?></label>
        <input type="text" id="region" name="region" class="form-control"
               value="<?= htmlspecialchars($settings['region'] ?? '') ?>">
    </div>

    <button type="submit" class="btn btn-primary"><?= $L['SETTINGS.SAVE'] ?></button>
</form>

<hr>


<?php
// Footer ausgeben
LBWeb::lbfooter();
?>
