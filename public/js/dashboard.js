document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("appModal");
    const openModalBtn = document.querySelector(".fa-square-plus");
    const closeModal = document.querySelector(".close");
    const appContainer = document.querySelector(".apps-container");

    // Modal öffnen
    openModalBtn.addEventListener("click", () => {
        modal.style.display = "flex";
    });

    // Modal schließen
    closeModal.addEventListener("click", () => {
        modal.style.display = "none";
    });

    // Funktion zum Erstellen einer neuen App mit Entfernen-Button
    function createApp(appName, iconClass, appID) {
        const appElement = document.createElement("div");
        appElement.classList.add("app");

        // App-Inhalt
        appElement.innerHTML = `
            <i class="${iconClass}"></i> &nbsp; ${appName}
            <button class="remove-app">&times;</button>
        `;

        // App entfernen bei Klick auf X
        appElement.querySelector(".remove-app").addEventListener("click", () => {
            removeUserApp(appID, appElement); // Entferne App aus der Datenbank und vom Dashboard
        });

        return appElement;
    }

    // Apps vom Backend holen und auf dem Dashboard anzeigen
    function loadUserApps() {
        fetch("../private/dashboard/get_user_apps.php")
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    data.apps.forEach(app => {
                        // Hier solltest du sicherstellen, dass du den richtigen Namen und Icon für jede App hast
                        const appID = app.appID;
                        const appName = app.appName;  // Platzhalter: Hier solltest du den echten Namen aus der DB holen
                        const iconClass = app.appIcon;  // Platzhalter: Hier das passende Icon ergänzen

                        const newApp = createApp(appName, iconClass, appID);
                        appContainer.appendChild(newApp);
                    });
                    disableAddedApps(data.apps); // Deaktiviere bereits hinzugefügte Apps
                } else {
                    console.log("Fehler beim Laden der Apps:", data.message);
                }
            })
            .catch(error => console.error("Fehler:", error));
    }

 // Deaktiviere die "Hinzufügen"-Buttons für Apps, die bereits hinzugefügt wurden
 function disableAddedApps(addedApps) {
    document.querySelectorAll(".add-app").forEach(button => {
        const appID = button.getAttribute("data-app");
        // Wenn die App bereits hinzugefügt wurde, deaktiviere den Button
        if (addedApps.some(app => app.appID == appID)) {
            button.disabled = true;  // Button deaktivieren
            button.textContent = "Bereits hinzugefügt";  // Optional: Text ändern
        }
    });
}

    // App hinzufügen
    document.querySelectorAll(".add-app").forEach(button => {
        button.addEventListener("click", () => {
            const appID = button.getAttribute("data-app"); // App ID holen
            const iconClass = button.querySelector("i").classList.value; // Icon mitnehmen
            const appName = button.querySelector(".app-title").textContent; // App Name

            const newApp = createApp(appName, iconClass, appID); // Übergabe von appID

            appContainer.appendChild(newApp);
            modal.style.display = "none"; // Schließen nach Auswahl

            // App zur Datenbank hinzufügen
            addUserApp(appID); // appID wird hier übergeben
        });
    });

    // Modal schließen, wenn außerhalb geklickt wird
    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    // App zur Datenbank hinzufügen
    function addUserApp(appID) {
        console.log("AppID gesendet:", appID);  // Für Debugging
        fetch("../private/dashboard/add_user_app.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `appID=${appID}` // App-ID im Body senden
        })
        .then(response => response.text())  // Antwort im Textformat
        .then(data => {
            console.log(data);    // Antwort des Servers
        })
        .catch(error => console.error("Fehler:", error));
    }

    // App aus der Datenbank entfernen
    function removeUserApp(appID, appElement) {
        console.log("Entfernen der App mit ID:", appID); // Für Debugging
        fetch("../private/dashboard/remove_user_app.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `appID=${appID}` // App-ID im Body senden
        })
        .then(response => response.json())  // Erwartung einer JSON-Antwort
        .then(data => {
            console.log(data); // Antwort des Servers

            if (data.status === "success") {
                // Erfolgreich entfernt – App aus dem DOM entfernen
                appElement.remove();
            } else {
                console.error("Fehler beim Entfernen der App:", data.message);
            }
        })
        .catch(error => console.error("Fehler:", error));
    }

    // Benutzer-Apps beim Laden der Seite anzeigen
    loadUserApps();  // Hier rufst du die Funktion auf, um die Apps zu laden
});
