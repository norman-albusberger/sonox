<?php
require_once "loxberry_system.php";
require_once "loxberry_web.php";
require_once "Sonox.php";

// Sprachdatei einlesen
$L = LBSystem::readlanguage("language.ini");
$htmlhead = "<link rel='stylesheet' type='text/css' href='assets/styles.css?v=1.0'>";
$sonox = new Sonox();
$settings = $sonox->readSettings();

$clipsDir = $settings["clipsDir"]; // z. B. /data/clips
$clipsFullPath = LBPDATADIR ."/". $clipsDir;

echo $clipsFullPath;



// Alle MP3-Dateien im Clips-Ordner finden
$clips = glob($clipsFullPath."/*.mp3");

var_dump($clips);

if (!$clips) {
    $clips = []; // Falls keine Dateien vorhanden sind
}

// Titel und Navigation
require_once "navigation.php";
$navbar[1]['active'] = true;

// Header erstellen
LBWeb::lbheader($L['COMMON.TITLE'], "http://www.loxwiki.eu:80/x/2wzL", "help.html");

// Base URL dynamisch ermitteln
$server_ip = $_SERVER['SERVER_ADDR'];
$apiUrl = "http://{$server_ip}";
$apiPort = $settings["port"];
$api_base_url = "{$apiUrl}:{$apiPort}";

$index = 0;
?>
<script>
    const sonoxData = {
        apiUrl: "<?= $apiUrl ?>",
        apiPort: <?= $apiPort ?>
    }
</script>


<h2>Sonox API Tester</h2>
<p>Hier kannst du die verfügbaren Endpunkte der Sonos HTTP-API testen. Trage die benötigten Parameter ein und teste die
    Endpunkte direkt.</p>

<div class="accordion">
    <?php foreach ($sonox->getEndpoints() as $category => $actions): ?>
        <div class="accordion-section">
            <div class="accordion-header"><?= htmlspecialchars($category) ?></div>

            <div class="accordion-content">
                <table style="width: 100%" class="api-list">
                    <thead>
                    <tr>
                        <th>API-URL mit Eingabefeldern</th>
                        <th>Test</th>
                        <th>Kopieren</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php

                    foreach ($actions as $endpoint => $description): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($description) ?></strong><br>
                                <span><?= htmlspecialchars($api_base_url . $endpoint) ?></span>
                                <div class="input-group">
                                    <?php
                                    // Platzhalter in der URL erkennen und Eingabefelder generieren
                                    if (strpos($endpoint, '{room}') !== false): ?>
                                        <label for="param-room-<?= $index ?>">Raum:</label>
                                        <input type="text" id="param-room-<?= $index ?>" class="param-room"
                                               placeholder="z.B. Living Room">
                                    <?php endif; ?>

                                    <?php
                                    // Platzhalter in der URL erkennen und Eingabefelder generieren
                                    if (strpos($endpoint, '{volume}') !== false): ?>
                                        <label for="param-volume-<?= $index ?>">Volume:</label>
                                        <input type="text" id="param-volume-<?= $index ?>" class="param-room"
                                               placeholder="+2">
                                    <?php endif; ?>


                                    <?php if (strpos($endpoint, '{name}') !== false): ?>
                                        <label for="param-name-<?= $index ?>">Name:</label>
                                        <input type="text" id="param-name-<?= $index ?>" class="param-name"
                                               placeholder="z.B. Lieblingsplaylist">
                                    <?php endif; ?>

                                    <?php if (strpos($endpoint, '{value}') !== false): ?>
                                        <label for="param-value-<?= $index ?>">Wert:</label>
                                        <input type="text" id="param-value-<?= $index ?>" class="param-value"
                                               placeholder="z.B. 15">
                                    <?php endif; ?>

                                    <?php if (strpos($endpoint, '{phrase}') !== false): ?>
                                        <label for="param-phrase-<?= $index ?>">Phrase:</label>
                                        <input type="text" id="param-phrase-<?= $index ?>" class="param-phrase"
                                               placeholder="z.B. Hello, dinner is ready">

                                        <label for="param-language-<?= $index ?>">Sprache (optional):</label>
                                        <input type="text" id="param-language-<?= $index ?>" class="param-language"
                                               placeholder="z.B. en-us, de, nl">

                                        <label for="param-announceVolume-<?= $index ?>">Lautstärke (optional):</label>
                                        <input type="number" id="param-announceVolume-<?= $index ?>" class="param-announceVolume"
                                               placeholder="z.B. 50">
                                    <?php endif; ?>

                                    <?php if (strpos($endpoint, '{spotifyURI}') !== false): ?>
                                        <label for="param-spotifyURI-<?= $index ?>">Spotify URI:</label>
                                        <input type="text" id="param-spotifyURI-<?= $index ?>" class="param-spotifyURI"
                                               placeholder="z.B. spotify:track:4LI1ykYGFCcXPWkrpcU7hn">
                                    <?php endif; ?>

                                    <?php if (strpos($endpoint, 'song:{songID}') !== false): ?>
                                        <label for="param-songID-<?= $index ?>">Song ID:</label>
                                        <input type="text" id="param-songID-<?= $index ?>" class="param-songID"
                                               placeholder="z.B. 355363490">
                                    <?php endif; ?>

                                    <?php if (strpos($endpoint, 'album:{albumID}') !== false): ?>
                                        <label for="param-albumID-<?= $index ?>">Album ID:</label>
                                        <input type="text" id="param-albumID-<?= $index ?>" class="param-albumID"
                                               placeholder="z.B. B071918VCR">
                                    <?php endif; ?>
                                    <?php if (strpos($endpoint, '{clip}') !== false): ?>
                                        <label for="clip-<?= $index ?>">Sound:</label>
                                        <select id="clip-<?= $index ?>">
                                            <?php foreach ($clips as $clip): ?>
                                                <?php
                                                $clipName = basename($clip);
                                                ?>
                                                <option value="<?= htmlspecialchars($clipName) ?>"><?= htmlspecialchars($clipName) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="param-announceVolume-<?= $index ?>">Lautstärke (optional):</label>
                                        <input type="number" id="param-announceVolume-<?= $index ?>" class="param-announceVolume"
                                               placeholder="z.B. 50">
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <button class="test-btn" data-endpoint="<?= htmlspecialchars($endpoint) ?>"
                                        data-index="<?= $index ?>">Testen
                                </button>
                            </td>
                            <td>
                                <button class="copy-btn" data-endpoint="<?= htmlspecialchars($endpoint) ?>"
                                        data-index="<?= $index ?>">URL kopieren
                                </button>
                            </td>
                        </tr>
                        <?php $index++; // Index hochzählen ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>

    <?php endforeach; ?>
</div>

<div id="response-box" class="response-box">Hier wird die Antwort der API angezeigt...</div>

<script src='assets/index.js'></script>

<?php
LBWeb::lbfooter();
?>
