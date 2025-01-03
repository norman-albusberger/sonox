document.addEventListener("DOMContentLoaded", function () {
    const apiBaseUrl = `${sonoxData.apiUrl}:${sonoxData.apiPort}`
    const responseBox = document.getElementById("response-box");

    // Funktion zum Bauen der URL
    function buildApiUrl(button) {
        const endpoint = button.dataset.endpoint;
        const index = button.dataset.index;
        let url = endpoint;

        // Platzhalter und zugehörige Eingabefelder
        const placeholders = [
            {key: "{room}", id: `param-room-${index}`},
            {key: "{name}", id: `param-name-${index}`},
            {key: "{value}", id: `param-value-${index}`},
            {key: "{phrase}", id: `param-phrase-${index}`},
            {key: "{language}", id: `param-language-${index}`},
            {key: "{volume}", id: `param-volume-${index}`},
            {key: "{announceVolume}", id: `param-announceVolume-${index}`},
            {key: "{spotifyURI}", id: `param-spotifyURI-${index}`},
            {key: "song:{songID}", id: `param-songID-${index}`},
            {key: "album:{albumID}", id: `param-albumID-${index}`},
            {key: "{clip}", id: `clip-${index}`}
        ];

        placeholders.forEach(({ key, id }) => {
            if (url.includes(key)) {
                const input = document.getElementById(id);
                if (!input || !input.value.trim()) {
                    alert(`Bitte einen Wert für ${key} eingeben.`);
                    throw new Error(`Fehlender Wert für ${key}`);
                }

                let value;

                // Prüfe den Typ des Elements
                if (input.tagName === "SELECT") {
                    // Für <select>-Felder den ausgewählten Wert verwenden
                    value = input.options[input.selectedIndex].value;
                } else {
                    // Für <input>-Felder den Standardwert verwenden
                    value = (key !== "{volume}") ? encodeURIComponent(input.value.trim()) : input.value.trim();
                }

                url = url.replace(key, value);
            }
        });

        let apiUrl = `${apiBaseUrl}${url}`;

        console.log(apiUrl)

        return apiUrl;
    }

    // API testen
    document.querySelectorAll(".test-btn").forEach(button => {

        button.addEventListener("click", function () {
            responseBox.textContent = "Loading..."
            try {
                const fullUrl = buildApiUrl(this);
                fetch(fullUrl)
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP-Error: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        responseBox.textContent = JSON.stringify(data, null, 2);
                    })
                    .catch(error => {
                        responseBox.textContent = `Error: ${error.message}`;
                    });
            } catch (error) {
                console.error(error.message);
            }
        });
    });

    // Accordion-Logik
    document.querySelectorAll(".accordion-header").forEach(header => {
        header.addEventListener("click", () => {
            const content = header.nextElementSibling;
            content.style.display = content.style.display === "block" ? "none" : "block";
        });
    });

    // URL kopieren
    // URL kopieren
    document.querySelectorAll(".copy-btn").forEach(button => {
        button.addEventListener("click", function () {
            const fullUrl = buildApiUrl(this);

            // Versuche, die URL in die Zwischenablage zu kopieren
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(fullUrl)
                    .then(() => {
                        alert('API-URL wurde in die Zwischenablage kopiert: ' + fullUrl);
                    })
                    .catch(err => {
                        console.error('Fehler beim Kopieren in die Zwischenablage:', err);
                    });
            } else {
                // Fallback: Temporäres Textfeld verwenden
                const tempInput = document.createElement("textarea");
                tempInput.value = fullUrl;
                document.body.appendChild(tempInput);
                tempInput.select();
                try {
                    document.execCommand("copy");
                    alert('API-URL wurde in die Zwischenablage kopiert: ' + fullUrl);
                } catch (err) {
                    console.error('Fehler beim Kopieren in die Zwischenablage:', err);
                }
                document.body.removeChild(tempInput);
            }
        });
    });

});