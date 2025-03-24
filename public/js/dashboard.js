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
    function createApp(appName, iconClass) {
        const appElement = document.createElement("div");
        appElement.classList.add("app");

        // App-Inhalt
        appElement.innerHTML = `
            <i class="${iconClass}"></i> &nbsp; ${appName}
            <button class="remove-app">&times;</button>
        `;

        // App entfernen bei Klick auf X
        appElement.querySelector(".remove-app").addEventListener("click", () => {
            appElement.remove();
        });

        return appElement;
    }

    // App hinzufügen
    document.querySelectorAll(".add-app").forEach(button => {
        button.addEventListener("click", () => {
            const appName = button.getAttribute("data-app");
            const iconClass = button.querySelector("i").classList.value; // Icon mitnehmen
            const newApp = createApp(appName, iconClass);

            appContainer.appendChild(newApp);
            modal.style.display = "none"; // Schließen nach Auswahl
        });
    });

    // Modal schließen, wenn außerhalb geklickt wird
    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
});
