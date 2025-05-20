document.addEventListener('DOMContentLoaded', function() {   
    // Fonction pour afficher un message d'erreur
    function showError(input, message) {
        const errorDiv = input.parentElement.querySelector(".error-message");
        errorDiv.textContent = message;
    }

    // Fonction pour effacer le message d'erreur
    function clearError(input) {
        const errorDiv = input.parentElement.querySelector(".error-message");
        errorDiv.textContent = "";
    }

    // Fonction pour valider la date
    function valideDate(input) {
        const date = input.value.trim();  // Utiliser la valeur de l'input (pas form.date)
        clearError(input);

        // Validation du format de la date
        if (!dateOk(date)) {
            showError(input, "Le format de la date est invalide. Utilisez le format JJ/MM/AAAA.");
        }
        
        // Validation de la date (doit être au plus tard aujourd'hui)
        const dateEntree = new Date(date.split('-').reverse().join('/')); // Convertir la date du format YYYY-MM-DD en un objet Date
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Réinitialiser l'heure de "today" à minuit

        if (dateEntree > today) {
            showError(input, "La date ne peut pas être dans le futur.");
        }
    }

    // Fonction de validation du format de la date
    function dateOk(dateString) {
        const dateParts = dateString.split('-');
        const day = parseInt(dateParts[2], 10);
        const month = parseInt(dateParts[1], 10) - 1; // Les mois sont indexés à partir de 0
        const year = parseInt(dateParts[0], 10);

        const date = new Date(year, month, day);
        return date.getDate() === day && date.getMonth() === month && date.getFullYear() === year;
    }

    // EventListener sur les changements de date
    const dateInput = document.querySelector('input#date');
    dateInput.addEventListener('change', function() {
        valideDate(dateInput);  // Valider la date lorsqu'elle change
    });

    // Fonction pour mettre à jour la couleur de l'input
    function updateColor(input) {
        input.classList.remove("A", "EA", "NA");
        const value = input.value.trim();
        if (value === "A") input.classList.add("A");
        else if (value === "EA") input.classList.add("EA");
        else if (value === "NA") input.classList.add("NA");
    }

    // Fonction pour afficher un message d'erreur
    function showError(input, message) {
        const errorDiv = input.parentElement.querySelector(".error-message");
        errorDiv.textContent = message;
    }

    // Fonction pour effacer le message d'erreur
    function clearError(input) {
        const errorDiv = input.parentElement.querySelector(".error-message");
        errorDiv.textContent = "";
    }

    // Fonction pour valider la note (nombre positif avec max 2 chiffres après la virgule)
    function validateNote(input) {
        const value = input.value.trim();
        const regex = /^\d+(\.\d{1,2})?$/; // Expression régulière pour valider un nombre positif avec max 2 chiffres après la virgule

        updateColor(input);

        if (value && !regex.test(value)) {
            showError(input, "Entrez un nombre positif avec maximum 2 décimales");
        } else {
            clearError(input);
        }
    }

    // Appliquer la validation de la note sur les inputs
    document.querySelectorAll("input.note").forEach(input => {
        input.addEventListener("input", () => validateNote(input));
    });

    const form = document.querySelector('form');
    const resultat = document.createElement('div');
    form.appendChild(resultat);  // Ajoute la zone d'erreur ou de succès sous le formulaire

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Empêche l'envoi du formulaire classique

        // Vérifie qu'aucun message d'erreur n'est affiché
        const hasVisibleErrors = Array.from(document.querySelectorAll('.error-message'))
            .some(div => div.textContent.trim() !== '');

        if (hasVisibleErrors) {
            return afficherErreur("Veuillez corriger toutes les erreurs avant d'envoyer.");
        }

        // Fonction pour afficher les erreurs
        function afficherErreur(message) {
            resultat.innerHTML = `
                <p style="color: red; text-align: center; font-weight: bold; border: 1px solid red">${message}</p>
            `;
        }
        
        // Fonction pour afficher le succès
        function afficherSuccès(message) {
            resultat.innerHTML = `<p style="color: green; text-align: center; font-weight: bold; border: 1px solid green">${message}</p>`;
        }
        
        if (!testType) {
            afficherErreur("Veuillez choisir un test dans la liste déroulante.");
            return;
        }

        // Si tout est OK, on envoie les données par AJAX
        const joueurs = [];
        const lignes = document.querySelectorAll('tbody tr');
        const dateGlobale = dateInput.value.trim();
        const testType = document.querySelector('#testType').value;
        const equipe = new URLSearchParams(window.location.search).get('id_eq');

        lignes.forEach((ligne) => {
            const id = ligne.querySelector('input[name=".id_joueur"]')?.value;
            if (!id) return;

            const note = ligne.querySelector('input[name="note"]')?.value || '';

            const joueur = {
                id_joueur: id,
                date: dateGlobale,
                test: testType,
                val: note,
            };
            joueurs.push(joueur);
        });

        fetch('save_testphysique.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ joueurs })
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes("ok")) {
                afficherSuccès("Réponses envoyées avec succès.");
                setTimeout(() => {
                    window.location.href = `/vizia/Staff/sectiontests.php?id_eq=${equipe}`;
                }, 1000); // Fermer setTimeout ici
            } else {
                afficherErreur("Erreur serveur : " + data);
            }
        })
        .catch(() => {
            afficherErreur("Erreur serveur ou réseau.");
        });
    });

});