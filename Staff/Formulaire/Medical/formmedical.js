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
        const duree = form.duree.value.trim().toLowerCase();

        // Validation des champs
        const dureeOk = /(jour|semaine|mois|année)s?/.test(duree);
        const dateOk = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/.test(date); // format JJ/MM/AAAA
        const graviteOk = /^\d+$/.test(gravite) && gravite >= 1 && gravite <= 10;;
        const champsNonVides = type && gravite && date && duree;

        // Vérification des champs vides (excepté observation)
        if (!champsNonVides) {
            return afficherErreur("Tous les champs sauf observation doivent être remplis.");
        }

        // Validation durée
        if (!dureeOk) {
            return afficherErreur("La durée doit mentionner 'jour', 'semaine', 'mois' ou 'année'.");
        }

        // Validation format date
        if (!dateOk) {
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
        formData.append('duree', duree);
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
            resultat.innerHTML = `<p style="color: red;">${message}</p>`;
        }

        // Fonction pour afficher le succès
        function afficherSuccès(message) {
            resultat.innerHTML = `<p style="color: green;">${message}</p>`;
        }
    });
});
