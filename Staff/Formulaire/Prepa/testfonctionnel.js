document.addEventListener('DOMContentLoaded', function() {

    // Fonction pour mettre à jour la couleur de l'input
    function updateColor(input) {
        input.classList.remove("A", "EA", "NA");
        const value = input.value.trim().toUpperCase();
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
    
    // Fonction pour valider la note
    function validateNote(input) {
        const value = input.value.trim().toUpperCase();
        updateColor(input);
        if (value && !["A", "EA", "NA"].includes(value)) {
            showError(input, "A, EA ou NA uniquement");
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
        
        // Récupérer la valeur du champ date
        const date = form.date.value.trim();
        
        // Validation du format de la date
        if (!dateOk(date)) {
            return afficherErreur("Le format de la date est invalide. Utilisez le format JJ/MM/AAAA.");
        }

        // Validation date (doit être au plus tard aujourd'hui)
        const dateEntree = new Date(date.split('/').reverse().join('/')); // Convertit la date du format JJ/MM/AAAA en objet Date
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Réinitialiser l'heure de "today" à minuit

        if (dateEntree > today) {
            return afficherErreur("La date ne peut pas être dans le futur.");
        }

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
            resultat.innerHTML = `<p style="color: green;">${message}</p>`;
        }

        // Fonction de validation de la date
        function dateOk(dateString) {
            const dateParts = dateString.split('-');
            const day = parseInt(dateParts[2], 10);
            const month = parseInt(dateParts[1], 10) - 1; // Les mois sont indexés à partir de 0
            const year = parseInt(dateParts[0], 10);

            const date = new Date(year, month, day);
            console.log(date);
            return date.getDate() === day && date.getMonth() === month && date.getFullYear() === year;
        }



        // Si tout est OK, on envoie les données par AJAX
        const formData = new URLSearchParams();
        const lignes = document.querySelectorAll('tbody tr');

        lignes.forEach((ligne, index) => {
            const id = ligne.querySelector('input[name^=".id_joueur"]')?.value;

            const squat = ligne.querySelector('input[name^="squat"]')?.value || '';
            const iso = ligne.querySelector('input[name^="iso"]')?.value || '';
            const souplesse = ligne.querySelector('input[name^="souplesse"]')?.value || '';
            const flamant = ligne.querySelector('input[name^="flamant"]')?.value || '';
            const haut = ligne.querySelector('input[name^="haut"]')?.value || '';

            formData.append(`joueurs[${index}][id_joueur]`, id);
            formData.append(`joueurs[${index}][date]`, dateGlobale);
            formData.append(`joueurs[${index}][squat]`, squat);
            formData.append(`joueurs[${index}][iso]`, iso);
            formData.append(`joueurs[${index}][souplesse]`, souplesse);
            formData.append(`joueurs[${index}][flamant]`, flamant);
            formData.append(`joueurs[${index}][haut]`, haut);
        });

        fetch('???.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        })
        .then(() => {
            afficherSuccès("Réponses envoyés avec succès.");
        })
        .catch(() => {
            afficherErreur("Erreur serveur ou réseau.");
        });
    });
});
