document.addEventListener("DOMContentLoaded", function () {
    const apiBaseUrl = `${sonoxData.apiUrl}:${sonoxData.apiPort}`;
    const playerSelects = document.querySelectorAll('[data-select="param-room"], [data-select="param-roomName"]');
    const favoriteSelects = document.querySelectorAll('[data-select="param-favoriteName"]');
    const playlistSelects = document.querySelectorAll('[data-select="param-playlistName"]');

    let zones = [];
    let isLoadingZones = false;


    // Funktion, um die Zonen-Daten zu laden
    async function loadZones() {
        if (isLoadingZones) return; // Verhindert parallele Aufrufe
        isLoadingZones = true;
        try {
            const response = await fetch(`${apiBaseUrl}/zones`);
            zones = await response.json();
        } catch (err) {
            console.error("Zone data not available", err);
            zones = [];
        } finally {
            isLoadingZones = false;
        }

    }


    // Funktion zum Bauen der URL
    function buildApiUrl(button) {
        const endpoint = button.dataset.endpoint;
        const index = button.dataset.index;
        let url = endpoint;

        const placeholders = [
            {key: "{room}", id: `param-room-${index}`},
            {key: "{roomName}", id: `param-roomName-${index}`},
            {key: "{name}", id: `param-name-${index}`},
            {key: "{playlistName}", id: `param-playlistName-${index}`},
            {key: "{favoriteName}", id: `param-favoriteName-${index}`},
            {key: "{value}", id: `param-value-${index}`},
            {key: "{preset}", id: `param-json-${index}`},
            {key: "{phrase}", id: `param-phrase-${index}`},
            {key: "{language}", id: `param-language-${index}`},
            {key: "{volume}", id: `param-volume-${index}`},
            {key: "{announceVolume}", id: `param-announceVolume-${index}`},
            {key: "{spotifyURI}", id: `param-spotifyURI-${index}`},
            {key: "song:{songID}", id: `param-songID-${index}`},
            {key: "album:{albumID}", id: `param-albumID-${index}`},
            {key: "{clip}", id: `clip-${index}`}
        ];

        placeholders.forEach(({key, id}) => {
            if (url.includes(key)) {
                const input = document.getElementById(id);
                // Überprüfen, ob das Eingabefeld existiert und einen Wert hat
                if (!input || (input.tagName === "INPUT" || input.tagName === "TEXTAREA") && !input.value.trim()) {
                    alert(`Bitte einen Wert für ${key} eingeben.`);
                    throw new Error(`Fehlender Wert für ${key}`);
                }

                let value = input.tagName === "SELECT"
                    ? input.options[input.selectedIndex].value
                    : key !== "{volume}"
                        ? encodeURIComponent(input.value.trim())
                        : input.value.trim();

                url = url.replace(key, value);
            }
        });

        return `${apiBaseUrl}${url}`;
    }

    // API-Test-Button
    document.querySelectorAll(".test-btn").forEach(button => {
        button.addEventListener("click", async () => {
            // Setze eine "Lade"-Nachricht ins Modal
            const responseContent = document.getElementById("apiResponseContent");
            responseContent.textContent = "Loading...";

            try {
                const fullUrl = buildApiUrl(button);

                // API-Aufruf
                const response = await fetch(fullUrl);
                if (!response.ok) {
                    throw new Error(`HTTP-Error: ${response.status}`);
                }

                // Daten der API abholen
                const data = await response.json();
                responseContent.textContent = JSON.stringify(data, null, 2);

                // Modal öffnen
                $("#apiResponseModal").popup("open", {transition: "fade"});

                // Zonen aktualisieren und anschließend die Übersicht aktualisieren
                await updatePlayerOverview();
            } catch (error) {
                responseContent.textContent = `Error: ${error.message}`;
                console.error("Fehler:", error.message);

            }
        });
    });


    async function updatePlayerOverview() {
        await loadZones();
        const apiStatusElement = document.getElementById("api-status");
        const tableBody = document.querySelector("#player-overview-table tbody");
        tableBody.innerHTML = ""; // Tabelle leeren

        if (zones.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="12">No Players available</td></tr>`;
            apiStatusElement.className = "ui-body-a ui-content ui-corner-all";
            apiStatusElement.style.border = "2px solid #F44336";
            apiStatusElement.style.backgroundColor = "#FFEBEE";
            apiStatusElement.style.color = "#C62828";
            apiStatusElement.innerHTML = '<strong>API Status:</strong> API service is not reachable.';
        } else {
            apiStatusElement.className = "ui-body-a ui-content ui-corner-all";
            apiStatusElement.style.border = "2px solid #4CAF50";
            apiStatusElement.style.backgroundColor = "#E8F5E9";
            apiStatusElement.style.color = "#2E7D32";
            apiStatusElement.innerHTML = '<strong>API Status:</strong> API service is up and running.';
            renderPlayerOverview(tableBody);

        }
    }

    function renderPlayerOverview(tableBody) {
        zones.forEach(zone => {
            // Eine Zone markieren
            const zoneRow = document.createElement("tr");
            zoneRow.innerHTML = `
            <td colspan="12" style="background-color: #f0f0f0; font-weight: bold;">
                Zone: ${zone.coordinator.roomName} (UUID: ${zone.uuid})
            </td>
        `;
            tableBody.appendChild(zoneRow);

            zone.members.forEach(member => {
                const state = member.state;
                const albumArtUri = state.currentTrack.absoluteAlbumArtUri || "https://fakeimg.pl/100x100?text=No+Album-Art";

                // Markiere den Coordinator der Zone
                const isCoordinator = member.uuid === zone.coordinator.uuid;
                const coordinatorLabel = isCoordinator ? " (Leader)" : "";

                // Eine Zeile für jeden Player hinzufügen
                const row = document.createElement("tr");
                row.innerHTML = `
                <td><img src="${albumArtUri}" alt="Album Art" class="album-art"></td>
                <td>${member.roomName}${coordinatorLabel}</td>
                <td>${state.currentTrack.title || "-"}</td>
                <td>${state.currentTrack.artist || "-"}</td>
                <td>${state.currentTrack.album || "-"}</td>
                <td>${state.currentTrack.stationName || "-"}</td>
                <td>${state.playbackState}</td>
                <td>${state.volume}%</td>
                <td>${state.mute ? "X" : ""}</td>
                <td>
                    Bass: ${state.equalizer.bass}, 
                    Treble: ${state.equalizer.treble}, 
                    Loudness: ${state.equalizer.loudness ? "X" : ""}
                </td>
                <td>${state.elapsedTimeFormatted || "00:00:00"}</td>
            `;
                tableBody.appendChild(row);
            });
        });
    }


// Funktion zur Befüllung der Select-Felder
    function populatePlayerSelects() {
        const players = zones.flatMap(zone => zone.members.map(member => member.roomName));

        playerSelects.forEach(select => {
            players.forEach(player => {
                const option = document.createElement("option");
                option.value = player;
                option.textContent = player;
                select.appendChild(option);
            });
        });
    }

    async function populateDropdown(endpoint, selects) {
        try {
            // Daten vom angegebenen Endpunkt abrufen
            const response = await fetch(endpoint);
            const items = await response.json();

            console.log(`Fetched items from ${endpoint}:`, items);

            // Jedes Select-Element mit den abgerufenen Daten befüllen
            selects.forEach(select => {
                // Vorherige Optionen entfernen
                select.innerHTML = "";

                items.forEach(item => {
                    const option = document.createElement("option");
                    option.value = option.textContent = item;
                    select.appendChild(option);
                });
            });
        } catch (err) {
            console.error(`Error during fetching data from ${endpoint}:`, err);
        }
    }

    document.querySelectorAll(".copy-btn, .copy-path-btn").forEach(button => {
        button.addEventListener("click", function () {
            let textToCopy;
            const fullUrl = buildApiUrl(this);
            if (this.classList.contains("copy-btn")) {
                if (!fullUrl) return; // Abbrechen, falls buildApiUrl null zurückgibt
                textToCopy = new URL(fullUrl);
            } else if (this.classList.contains("copy-path-btn")) {
                // Für den Button mit der Klasse 'copy-path-btn' nur den Endpunkt kopieren
                textToCopy = new URL(fullUrl).pathname;
            }

            // Kopieren in die Zwischenablage
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textToCopy)
                    .then(() => {
                        alert('Kopiert: ' + textToCopy);
                    })
                    .catch(err => {
                        console.error('Fehler beim Kopieren in die Zwischenablage:', err);
                    });
            } else {
                // Fallback: Temporäres Textfeld verwenden
                const tempInput = document.createElement("textarea");
                tempInput.value = textToCopy;
                document.body.appendChild(tempInput);
                tempInput.select();
                try {
                    document.execCommand("copy");
                    alert('Kopiert: ' + textToCopy);
                } catch (err) {
                    console.error('Fehler beim Kopieren in die Zwischenablage:', err);
                }
                document.body.removeChild(tempInput);
            }
        });
    });

    function generatePreset(zone) {
        // Beispielhaft ein Preset-Objekt erstellen
        return {
            players: zone.members.map(member => ({
                roomName: member.roomName,
                volume: member.state.volume
            })),
            playMode: {
                shuffle: zone.coordinator.state.playMode.shuffle,
                repeat: zone.coordinator.state.playMode.repeat,
                crossfade: zone.coordinator.state.playMode.crossfade
            },
            pauseOthers: false,
            playlistUri: zone.coordinator.state.currentTrack.uri || "",
            volume: zone.coordinator.groupState.volume
        };
    }

// Fülle die Textarea mit einem generierten Preset
    function populatePresetTextarea() {
        const textareas = document.querySelectorAll(".param-json");
        if (!zones || zones.length === 0) {
            console.warn("Keine Zonen verfügbar, Preset kann nicht generiert werden.");
            return;
        }

        textareas.forEach((textarea, index) => {
            const zone = zones[index % zones.length]; // Nimm eine Zone basierend auf dem Index
            const preset = generatePreset(zone);
            textarea.value = JSON.stringify(preset, null, 2); // Formatieren als JSON
        });
    }

// Hauptfunktion
    async function initialize() {
        await updatePlayerOverview(); // Tabelle aktualisieren
        populatePresetTextarea(); // Textareas befüllen
        populatePlayerSelects();
        // Favoriten befüllen
        await populateDropdown(`${apiBaseUrl}/favorites`, favoriteSelects);

// Playlisten befüllen
        await populateDropdown(`${apiBaseUrl}/playlists`, playlistSelects);

    }

    (async () => {
        await initialize();
        // Alle 5 Sekunden die Funktion ausführen
        setInterval(() => {
            updatePlayerOverview().catch(err => console.error("Error in setInterval:", err));
        }, 5000);
    })();


});
