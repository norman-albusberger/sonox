<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
$L = LBSystem::readlanguage("language.ini");

class Sonox
{
    private string $settingsPath;
    private array $endpoints;


    /**
     * Konstruktor der Klasse
     *
     * @param string $settingsPath Pfad zur settings.json
     */
    public function __construct(string $settingsPath = LBPDATADIR . "/settings.json")
    {
        $this->settingsPath = $settingsPath;
        $this->initializeEndpoints();

    }

    /**
     * Gibt die Liste der Endpunkte zurück.
     *
     * @return array Die Endpunkte als Array.
     */
    public function getEndpoints(): array
    {
        return $this->endpoints;
    }

    /**
     * Gibt die Endpunkte für eine spezifische Kategorie zurück.
     *
     * @param string $category Die Kategorie der Endpunkte.
     * @return array|null Die Endpunkte der Kategorie oder null, wenn sie nicht existiert.
     */
    public function getEndpointsByCategory(string $category): ?array
    {
        return $this->endpoints[$category] ?? null;
    }


    private function initializeEndpoints(): void
    {
        global $L; // Zugriff auf die Sprachvariablen
        $this->endpoints = [
            $L['ENDPOINTS.SYSTEM_ACTIONS'] => [
                "/zones" => $L['ENDPOINTS.LIST_ZONES'],
                "/pauseall" => $L['ENDPOINTS.PAUSE_ALL'],
                "/resumeall" => $L['ENDPOINTS.RESUME_ALL'],
                //"/reindex" => $L['ENDPOINTS.REINDEX_LIBRARY'],
                "/favorites" => $L['ENDPOINTS.FAVORITES']
            ],
            $L['ENDPOINTS.ROOM_ACTIONS'] => [
                "/{room}/volume/{volume}" => $L['ENDPOINTS.SET_VOLUME'],
                "/{room}/groupvolume/{volume}" => $L['ENDPOINTS.SET_GROUP_VOLUME'],
                "/{room}/play" => $L['ENDPOINTS.PLAY_ROOM'],
                "/{room}/pause" => $L['ENDPOINTS.PAUSE_ROOM'],
                "/{room}/playpause" => $L['ENDPOINTS.TOGGLE_PLAY_PAUSE'],
                "/{room}/mute" => $L['ENDPOINTS.MUTE_ROOM'],
                "/{room}/unmute" => $L['ENDPOINTS.UNMUTE_ROOM'],
                "/{room}/togglemute" => $L['ENDPOINTS.TOGGLE_MUTE'],
                "/{room}/next" => $L['ENDPOINTS.SKIP_NEXT'],
                "/{room}/previous" => $L['ENDPOINTS.SKIP_PREVIOUS'],
                "/{room}/state" => $L['ENDPOINTS.GET_STATE'],
                "/{room}/sleep/{timeout}" => $L['ENDPOINTS.SET_SLEEP_TIMER']
            ],

            $L['ENDPOINTS.GROUP_ACTIONS'] => [
                "/{room}/add/{roomName}" => $L['ENDPOINTS.ADD_TO_GROUP'], // Adds the specified room ({roomName}) to the group of the room ({room}).
                "/{room}/isolate" => $L['ENDPOINTS.ISOLATE_PLAYER'], // Isolates the specified room ({room}), making it a standalone player.
                "/{room}/ungroup" => $L['ENDPOINTS.UNGROUP_PLAYER'], // Ungroups the specified room ({room}), alias for isolate.
                "/{room}/leave" => $L['ENDPOINTS.LEAVE_GROUP'], // Makes the specified room ({room}) leave its current group, alias for isolate.
                "/{room}/join/{roomName}" => $L['ENDPOINTS.JOIN_GROUP'] // Makes the specified room ({room}) join the group of another room ({roomName}).
            ],


            $L['ENDPOINTS.PLAYLIST_ACTIONS'] => [
                "/{room}/favorite/{favoriteName}" => $L['ENDPOINTS.PLAY_FAVORITE'],
                "/{room}/playlist/{playlistName}" => $L['ENDPOINTS.PLAY_PLAYLIST'],
                "/{room}/queue" => $L['ENDPOINTS.GET_QUEUE'],
                "/{room}/clearqueue" => $L['ENDPOINTS.CLEAR_QUEUE']
            ],
            $L['ENDPOINTS.TTS'] => [
                "/{room}/say/{phrase}/{language}/{announceVolume}" => $L['ENDPOINTS.TTS_ROOM'],
                "/sayall/{phrase}/{language}/{announceVolume}" => $L['ENDPOINTS.TTS_ALL']
            ],
            /*  $L['ENDPOINTS.STREAMING_SERVICES'] => [
                  // Spotify
                  "/{room}/spotify/now/{spotifyURI}" => $L['ENDPOINTS.SPOTIFY_NOW'],
                  "/{room}/spotify/next/{spotifyURI}" => $L['ENDPOINTS.SPOTIFY_NEXT'],
                  "/{room}/spotify/queue/{spotifyURI}" => $L['ENDPOINTS.SPOTIFY_QUEUE'],

                  // Apple Music
                  "/{room}/applemusic/now/song:{songID}" => $L['ENDPOINTS.APPLEMUSIC_SONG_NOW'],
                  "/{room}/applemusic/next/song:{songID}" => $L['ENDPOINTS.APPLEMUSIC_SONG_NEXT'],
                  "/{room}/applemusic/queue/song:{songID}" => $L['ENDPOINTS.APPLEMUSIC_SONG_QUEUE'],
                  "/{room}/applemusic/now/album:{albumID}" => $L['ENDPOINTS.APPLEMUSIC_ALBUM_NOW'],
                  "/{room}/applemusic/next/album:{albumID}" => $L['ENDPOINTS.APPLEMUSIC_ALBUM_NEXT'],
                  "/{room}/applemusic/queue/album:{albumID}" => $L['ENDPOINTS.APPLEMUSIC_ALBUM_QUEUE'],

                  // Amazon Music
                  "/{room}/amazonmusic/now/song:{songID}" => $L['ENDPOINTS.AMAZONMUSIC_SONG_NOW'],
                  "/{room}/amazonmusic/next/song:{songID}" => $L['ENDPOINTS.AMAZONMUSIC_SONG_NEXT'],
                  "/{room}/amazonmusic/queue/song:{songID}" => $L['ENDPOINTS.AMAZONMUSIC_SONG_QUEUE'],
                  "/{room}/amazonmusic/now/album:{albumID}" => $L['ENDPOINTS.AMAZONMUSIC_ALBUM_NOW'],
                  "/{room}/amazonmusic/next/album:{albumID}" => $L['ENDPOINTS.AMAZONMUSIC_ALBUM_NEXT'],
                  "/{room}/amazonmusic/queue/album:{albumID}" => $L['ENDPOINTS.AMAZONMUSIC_ALBUM_QUEUE']
              ],*/
            $L['ENDPOINTS.CLIPS'] => [
                "/{room}/clip/{clip}/{announceVolume}" => $L['ENDPOINTS.PLAY_CLIP'],
                "/clipall/{clip}/{announceVolume}" => $L['ENDPOINTS.PLAY_CLIP_ALL'],
                "/clipavailable/{clip}/{announceVolume}" => $L['ENDPOINTS.PLAY_CLIP_AVAILABLE']
            ]
        ];
    }

    /**
     * Liest die Einstellungen aus der settings.json.
     *
     * @return array Enthält die aktuellen Einstellungen als Array.
     */
    public function readSettings(): array
    {
        if (!file_exists($this->settingsPath)) {
            return []; // Rückgabe eines leeren Arrays, wenn die Datei nicht existiert
        }

        $settings = file_get_contents($this->settingsPath);
        return json_decode($settings, true) ?? []; // Rückgabe eines Arrays oder eines leeren Arrays bei Fehlern
    }

    /**
     * Speichert neue Einstellungen in der settings.json.
     * Bestehende Einstellungen werden gemerged.
     *
     * @param array $newSettings Die neuen Einstellungen als Array.
     * @return bool Gibt true zurück, wenn das Speichern erfolgreich war, sonst false.
     */
    public function saveSettings(array $newSettings): bool
    {
        // Aktuelle Einstellungen laden
        $currentSettings = $this->readSettings();

        // Neue Einstellungen mit bestehenden zusammenführen
        $mergedSettings = array_merge($currentSettings, $newSettings);

        // Versuche, die Datei zu schreiben
        $result = file_put_contents(
            $this->settingsPath,
            json_encode($mergedSettings, JSON_PRETTY_PRINT)
        );

        return $result !== false;
    }

    /**
     * Neustart eines Systemd-Dienstes.
     *
     * @param string $serviceName Der Name des Dienstes, der neu gestartet werden soll.
     * @return array Enthält den Rückgabecode und die Ausgabe des Befehls.
     */
    public function restartService(string $serviceName = "sonos-http-api.service"): array
    {
        $restartCommand = escapeshellcmd("sudo systemctl restart $serviceName");
        $output = [];
        $returnCode = 0;

        exec($restartCommand, $output, $returnCode);

        // Rückgabe strukturierter Ergebnisse
        return [
            "returnCode" => $returnCode,
            "output" => $output,
            "success" => $returnCode === 0,
        ];
    }
}
