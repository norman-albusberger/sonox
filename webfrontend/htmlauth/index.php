<?php
require_once "loxberry_system.php";
require_once "loxberry_web.php";
require_once "Sonox.php";

// Sprachdatei einlesen
$L = LBSystem::readlanguage("language.ini");
$htmlhead = "<link rel='stylesheet' type='text/css' href='assets/styles.css?v=3.0'>";
$sonox = new Sonox();
$settings = $sonox->readSettings();

$clipsDir = $settings["clipsDir"]; // z. B. /data/clips
$clipsFullPath = LBPDATADIR . "/" . $clipsDir;

echo $clipsFullPath;

// Alle MP3-Dateien im Clips-Ordner finden
$clips = glob($clipsFullPath . "/*.mp3");

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

<h1>Overview of your Sonos Setup</h1>

<div class="ui-content">
    <table id="player-overview-table" class="state-table">
        <thead>
        <tr>
            <th></th>
            <th><?= $L['COMMON.ROOM']; ?></th>
            <th><?= $L['COMMON.PLAY_TITLE']; ?></th>
            <th><?= $L['COMMON.ARTIST']; ?></th>
            <th><?= $L['COMMON.ALBUM']; ?></th>
            <th><?= $L['COMMON.STATION']; ?></th>
            <th><?= $L['COMMON.ROOM']; ?></th>
            <th><?= $L['COMMON.VOLUME']; ?></th>
            <th><?= $L['COMMON.MUTE']; ?></th>
            <th>EQ</th>
            <th><?= $L['COMMON.DURATION']; ?></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td colspan="11">Data not available. Please check the connection to your Sonos System.</td>
        </tr>
        </tbody>
    </table>
</div>


<h1>SonoX Virtual Outputs Actions</h1>
<h2><?php echo $L['ENDPOINTS.TEST_DESCRIPTION']; ?></h2>

<?php foreach ($sonox->getEndpoints() as $category => $actions): ?>

    <div data-role="collapsible"
         id="coll_<?= strtolower($category) ?>"
         data-content-theme="true"
         data-collapsed="true"
         data-collapsed-icon="carat-d"
         data-expanded-icon="carat-u"
         data-iconpos="right"
         class="ui-collapsible ui-collapsible-inset ui-corner-all ui-collapsible-themed-content">

        <h2 class="ui-bar ui-bar-a ui-corner-all ui-collapsible-heading">

            <?= htmlspecialchars($category) ?>

        </h2>

        <table style="width: 100%" class="api-list">
            <thead>
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
                            // Platzhalter erkennen und Select-Felder generieren
                            if (strpos($endpoint, '{room}') !== false): ?>
                                <div class="ui-field-contain">
                                    <label for="param-room-<?= $index ?>"><?= $L['COMMON.ROOM'] ?>
                                        :</label>
                                    <select id="param-room-<?= $index ?>" data-select="param-room">
                                        <option value="" selected="selected"><?= $L['COMMON.SELECT_ROOM'] ?></option>
                                        <!-- Dynamisch mit JavaScript befüllen -->
                                    </select>
                                </div>
                            <?php endif; ?>

                            <?php
                            // Platzhalter in der URL erkennen und Eingabefelder generieren
                            if (strpos($endpoint, '{volume}')): ?>
                                <div class="ui-field-contain">
                                    <label for="param-volume-<?= $index ?>"><?= $L['COMMON.VOLUME'] ?>:</label>
                                    <input type="text" id="param-volume-<?= $index ?>" class="param-room"
                                           placeholder="+2">
                                </div>
                            <?php endif; ?>


                            <?php if (strpos($endpoint, '{name}')): ?>
                                <div class="ui-field-contain">
                                    <label for="param-name-<?= $index ?>"><?= $L['COMMON.NAME'] ?>:</label>
                                    <input type="text" id="param-name-<?= $index ?>" class="param-name"
                                           placeholder="z.B. Lieblingsplaylist">
                                </div>
                            <?php endif; ?>

                            <?php if (strpos($endpoint, '{value}')): ?>
                                <div class="ui-field-contain">
                                    <label for="param-value-<?= $index ?>"><?= $L['COMMON.VALUE'] ?>:</label>
                                    <input type="text" id="param-value-<?= $index ?>" class="param-value"
                                           placeholder="z.B. 15">
                                </div>
                            <?php endif; ?>

                            <?php if (strpos($endpoint, '{phrase}')): ?>
                                <div class="ui-field-contain">
                                    <label for="param-phrase-<?= $index ?>"><?= $L['COMMON.PHRASE'] ?>:</label>
                                    <input type="text" id="param-phrase-<?= $index ?>" class="param-phrase"
                                           placeholder="z.B. Hello, dinner is ready">

                                    <label for="param-language-<?= $index ?>"><?= $L['COMMON.LANG'] ?>
                                        (optional):</label>
                                    <input type="text" id="param-language-<?= $index ?>" class="param-language"
                                           placeholder="z.B. en-us, de, nl">

                                    <label for="param-announceVolume-<?= $index ?>">Lautstärke (optional):</label>
                                    <input type="number" id="param-announceVolume-<?= $index ?>"
                                           class="param-announceVolume"
                                           placeholder="50">
                                </div>
                            <?php endif; ?>

                            <?php if (strpos($endpoint, '{spotifyURI}')): ?>
                                <label for="param-spotifyURI-<?= $index ?>">Spotify URI:</label>
                                <input type="text" id="param-spotifyURI-<?= $index ?>" class="param-spotifyURI"
                                       placeholder="z.B. spotify:track:4LI1ykYGFCcXPWkrpcU7hn">
                            <?php endif; ?>

                            <?php if (strpos($endpoint, 'song:{songID}')): ?>
                                <label for="param-songID-<?= $index ?>">Song ID:</label>
                                <input type="text" id="param-songID-<?= $index ?>" class="param-songID"
                                       placeholder="z.B. 355363490">
                            <?php endif; ?>

                            <?php if (strpos($endpoint, 'album:{albumID}')): ?>
                                <label for="param-albumID-<?= $index ?>">Album ID:</label>
                                <input type="text" id="param-albumID-<?= $index ?>" class="param-albumID"
                                       placeholder="z.B. B071918VCR">
                            <?php endif; ?>
                            <?php if (strpos($endpoint, 'preset')): ?>
                            <div class="ui-field-contain">
                                <label for="param-json-<?= $index ?>">Preset</label>
                                <textarea rows="15" id="param-json-<?= $index ?>" class="param-json"
                                          placeholder="json preset "></textarea>
                            </div>
                            <?php endif; ?>
                            <?php if (strpos($endpoint, '{clip}')): ?>
                                <label for="clip-<?= $index ?>"><?= $L['COMMON.CLIP'] ?>:</label>
                                <select id="clip-<?= $index ?>">
                                    <?php foreach ($clips as $clip): ?>
                                        <?php
                                        $clipName = basename($clip);
                                        ?>
                                        <option value="<?= htmlspecialchars($clipName) ?>"><?= htmlspecialchars($clipName) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="param-announceVolume-<?= $index ?>">Lautstärke (optional):</label>
                                <input type="number" id="param-announceVolume-<?= $index ?>"
                                       class="param-announceVolume"
                                       placeholder="z.B. 50">
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <button class="test-btn  ui-btn ui-btn-a ui-icon-action ui-btn-icon-left ui-shadow ui-corner-all"
                                data-endpoint="<?= htmlspecialchars($endpoint) ?>"
                                data-index="<?= $index ?>"><?= $L['ENDPOINTS.TEST_API'] ?>                            </button>
                    </td>
                    <td>
                        <button class="copy-btn  ui-btn ui-btn-a ui-shadow ui-corner-all"
                                data-endpoint="<?= htmlspecialchars($endpoint) ?>"
                                data-index="<?= $index ?>"><?= $L['ENDPOINTS.COPY_URL'] ?>
                        </button>
                    </td>
                    <td>
                        <button class="copy-path-btn  ui-btn ui-btn-a ui-shadow ui-corner-all"
                                data-endpoint="<?= htmlspecialchars($endpoint) ?>"
                                data-index="<?= $index ?>"><?= $L['ENDPOINTS.COPY_PATH'] ?>
                        </button>
                    </td>
                </tr>
                <?php $index++; // Index hochzählen ?>
            <?php endforeach; ?>
            </tbody>
        </table>

    </div>

<?php endforeach; ?>
</div>

<div id="response-box" class="response-box"><?= $L['ENDPOINTS.RESPONSE_PLACEHOLDER'] ?></div>

<script src='assets/index.js'></script>

<?php
LBWeb::lbfooter();
?>
