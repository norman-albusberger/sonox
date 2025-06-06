<?php
require_once "loxberry_system.php";
require_once "loxberry_io.php";
require_once "loxberry_web.php";
require_once "Sonox.php";

// Sprachdatei einlesen
$L = LBSystem::readlanguage("language.ini");
$htmlhead = "<link rel='stylesheet' type='text/css' href='assets/styles.css?v=3.1'>";
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
// MQTT-Verbindungsdetails abrufen
$mqttDetails = mqtt_connectiondetails();
$mqttUsername = $mqttDetails['brokeruser'];
$mqttPassword = $mqttDetails['brokerpass'];
$mqttBroker = $mqttDetails['brokeraddress'];


$index = 0;

?>
<div style="border-radius: 16px; background: linear-gradient(135deg, #666681, #2b2b44); padding: 2rem; color: #ffffff; font-family: sans-serif; max-width: 800px; margin: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
    <h1 style="margin-top: 0; font-size: 2rem;">SonoX-Pro – The Smart Bridge Between Sonos and Loxone</h1>
    <p style="font-size: 1.1rem; line-height: 1.6;">
        Unlock the full potential of your smart home: control your Sonos speakers from your Loxone system with unmatched speed and flexibility. SonoX-Pro brings seamless integration and powerful tools.
    </p>

    <ul style="list-style-type: '✔ '; padding-left: 1.2rem; margin-bottom: 1.5rem;">
        <li><strong>Built-in Preset Editor</strong> with instant preview and full control</li>
        <li>Now Playing Sync via Loxone Text Inputs</li>
        <li>Advanced Multiroom Synchronization</li>
        <li>Priority Audio Routing (e.g. for alarms or doorbells)</li>
        <li>Blazing Fast – designed for low-latency response</li>
    </ul>
    <p style="font-size: 1rem; font-style: italic; margin-top: -1rem; margin-bottom: 1.5rem;">
        Combine it with ElevenLabs and let your smart home talk back – with stunningly realistic voices for Text-to-Speech.
    </p>
    <a href="https://sonox.net/" target="_blank" style="display: inline-block; padding: 0.75rem 1.5rem; background-color: #119a09; color: #fff; font-weight: bold; text-decoration: none; border-radius: 8px;">
        Learn More & Get SonoX-Pro
    </a>
</div>

<script>
    const sonoxData = {
        apiUrl: "<?= $apiUrl ?>",
        apiPort: <?= $apiPort ?>,
        mqttUsername: "<?= $mqttUsername ?>",
        mqttPassword: "<?= $mqttPassword ?>",
        mqttBroker: "<?= $mqttBroker ?>"
    }
</script>
<!-- Modal für die API-Antwort -->
<div data-role="popup" id="apiResponseModal" data-overlay-theme="b" data-theme="a" data-dismissible="false" style="max-width: 90%; max-height: 80%; min-width: 300px; min-height: 400px">
    <div data-role="header" data-theme="a">
        <a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
        <h1>API Response</h1>
    </div>
    <div role="main" class="ui-content" style="overflow-y: auto; max-height: 60vh;">
        <pre id="apiResponseContent" style="white-space: pre-wrap; word-wrap: break-word;"></pre>
        <a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b" data-rel="back">Schließen</a>
    </div>
    <div class="sonox-banner">
        🚀 Upgrade to SonoX-Pro. With SonoX-Pro you can create powerful presets with custom endpoints, use lifelike TTS powered by Elevenlabs with over 20 natural human voices<br>
        <a href="https://sonox.net/product/sonox-loxberry-plugin/" class="sonox-banner-btn">Upgrade now</a>
    </div>

</div>
<h1>Overview of your Sonos Setup</h1>
<div id="api-status" class="ui-body-a ui-content ui-corner-all" style="margin: 10px 0; padding: 1em; border: 2px solid #4CAF50; background-color: #E8F5E9; color: #2E7D32;">
    <strong><i class="fa fa-info-circle" aria-hidden="true"></i> API Status:</strong> Verifying connection...
</div>

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
                        <strong> <?= $description ?></strong><br>
                        <span><?= htmlspecialchars($api_base_url . $endpoint) ?></span>
                        <?php
                        // Dynamisch mit JavaScript befüllen
                        $hasRoom = strpos($endpoint, '{room}') !== false;
                        $hasSecondRoom = strpos($endpoint, '{roomName}') !== false;
                        ?>
                        <div class="input-group">

                            <?php if ($hasRoom): ?>
                                <div class="ui-field-contain">
                                    <label for="param-room-<?= $index ?>"><?= $L['COMMON.ROOM'] ?>:</label>
                                    <select id="param-room-<?= $index ?>" data-select="param-room">
                                        <option value="" selected="selected"><?= $L['COMMON.SELECT_ROOM'] ?></option>
                                        <!-- Dynamisch mit JavaScript befüllen -->
                                    </select>
                                </div>
                            <?php endif; ?>

                            <?php if ($hasSecondRoom): ?>
                                <div class="ui-field-contain">
                                    <label for="param-roomName-<?= $index ?>"><?= $L['COMMON.ROOM_SECOND'] ?>:</label>
                                    <select id="param-roomName-<?= $index ?>" data-select="param-roomName">
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


                            <?php if (strpos($endpoint, '{favoriteName}')): ?>
                                <div class="ui-field-contain">
                                    <label for="param-favoriteName-<?= $index ?>"><?= $L['COMMON.NAME'] ?>:</label>
                                    <select id="param-favoriteName-<?= $index ?>" data-select="param-favoriteName">
                                        <option value="" selected="selected">Favorite</option>
                                        <!-- Dynamisch mit JavaScript befüllen -->
                                </div>
                            <?php endif; ?>
                            <?php if (strpos($endpoint, '{playlistName}')): ?>
                                <div class="ui-field-contain">
                                    <label for="param-playlistName-<?= $index ?>"><?= $L['COMMON.NAME'] ?>:</label>
                                    <select id="param-playlistName-<?= $index ?>" data-select="param-playlistName">
                                        <option value="" selected="selected">Playlist</option>
                                        <!-- Dynamisch mit JavaScript befüllen -->
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
                        <button class="copy-path-btn ui-btn ui-btn-a ui-shadow ui-corner-all"
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

<script src='assets/index.js?v=3.4'></script>

<?php
LBWeb::lbfooter();
?>
