document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const resultat = document.createElement('div');
    form.appendChild(resultat);  // Ajoute la zone d'erreur ou de succès sous le formulaire

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Empêche l'envoi du formulaire classique

        // Récupérer les valeurs des champs
        const type = form.type.value.trim();
        const gravite = form.gravite.value;
        const date = form.date.value.trim();
        const observation = form.observation.value.trim();

        // Validation des champs
        const graviteOk = /^\d+$/.test(gravite) && gravite >= 1 && gravite <= 10;
        const champsNonVides = type && gravite && date && duree;

        // Vérification des champs vides (excepté observation)
        if (!champsNonVides) {
            return afficherErreur("Tous les champs sauf observation doivent être remplis.");
        }

        // Validation format date
        if (!dateOk(date)) {
            return afficherErreur("Le format de la date est invalide.");
        }

        // Validation date (doit être au plus tard aujourd'hui)
        const dateEntree = new Date(date.split('/').reverse().join('/')); // Convertit la date du format JJ/MM/AAAA en objet Date
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Réinitialiser l'heure de "today" à minuit

        if (dateEntree > today) {
            return afficherErreur("La date ne peut pas être dans le futur.");
        }

        // Validation gravité
        if (!graviteOk) {
            return afficherErreur("La gravité doit être un entier.");
        }

        // Si tout est OK, on envoie les données par AJAX
        const formData = new URLSearchParams();
        formData.append('type', type);
        formData.append('gravite', gravite);
        formData.append('date', date);
        formData.append('observation', observation);
        formData.append('id_joueur', new URLSearchParams(window.location.search).get('id')); // récupère l'ID de l'URL

        fetch('formmedical.php', {
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

        // Fonction pour afficher les erreurs
        function afficherErreur(message) {
            resultat.innerHTML = `
                <p style="
                    color: white; 
                    text-align: center; 
                    font-weight: bold;
                    border: 1px solid white">${message}</p>`;
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
    });
});
