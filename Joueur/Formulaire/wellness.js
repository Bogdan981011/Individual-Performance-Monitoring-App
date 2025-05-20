document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const resultat = document.createElement('div');
    form.appendChild(resultat);

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Récupération des champs
        const date = form.date.value.trim();
        const sommeil = form.sommeil.value;
        const courbatureHaut = form.courbatureHaut.value;
        const courbatureBas = form.courbatureBas.value;
        const humeur = form.humeur.value;
        const observations = form.observations.value.trim();
        const csrfToken = form.querySelector('input[name="csrf_token"]').value;

        // Vérification des champs obligatoires
        if (!date || !sommeil || !courbatureHaut || !courbatureBas || !humeur) {
            return afficherErreur("Tous les champs sauf 'observations' doivent être remplis.");
        }

        // Validation format date
        if (!dateOk(date)) {
            return afficherErreur("Le format de la date est invalide.");
        }

        const dateEntree = new Date(date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (dateEntree > today) {
            return afficherErreur("La date ne peut pas être dans le futur.");
        }

        // Validation des champs numériques
        const isNumber = (val, min, max) => /^\d+$/.test(val) && val >= min && val <= max;

        if (!isNumber(sommeil, 1, 5)) return afficherErreur("Le score de sommeil doit être entre 1 et 5.");
        if (!isNumber(courbatureHaut, 0, 10)) return afficherErreur("Le score de courbature (haut) doit être entre 0 et 10.");
        if (!isNumber(courbatureBas, 0, 10)) return afficherErreur("Le score de courbature (bas) doit être entre 0 et 10.");
        if (!isNumber(humeur, 1, 5)) return afficherErreur("Le score d'humeur doit être entre 1 et 5.");

        if (observations.length > 500) {
            return afficherErreur("L'observation ne doit pas dépasser 500 caractères.");
        }

        // Préparation des données
        const formData = new FormData();
        formData.append('date', date);
        formData.append('sommeil', sommeil);
        formData.append('courbatureHaut', courbatureHaut);
        formData.append('courbatureBas', courbatureBas);
        formData.append('humeur', humeur);
        formData.append('observations', observations);
        formData.append('csrf_token', csrfToken);

        // Envoi AJAX
        fetch('save_wellness.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log("Réponse PHP :", data);
            if (data.toLowerCase().includes("ok")) {
                afficherSuccès("Données bien-être enregistrées avec succès.");
                setTimeout(() => {
                    window.location.href = "/vizia/Joueur/Formulaire/formulaires.html";
                }, 1000);
            } else {
                afficherErreur("Erreur serveur : " + data);
            }
        })
        .catch(() => {
            afficherErreur("Erreur serveur ou réseau.");
        });

        // Affichage erreurs
        function afficherErreur(message) {
            resultat.innerHTML = `
                <p style="color: white; text-align: center; font-weight: bold; border: 1px solid white;">
                    ${message}
                </p>`;
        }

        // Affichage succès
        function afficherSuccès(message) {
            resultat.innerHTML = `<p style="color: green; text-align: center;">${message}</p>`;
        }

        // Validation de date
        function dateOk(dateString) {
            const dateParts = dateString.split('-');
            const year = parseInt(dateParts[0], 10);
            const month = parseInt(dateParts[1], 10) - 1;
            const day = parseInt(dateParts[2], 10);

            const date = new Date(year, month, day);
            return date.getFullYear() === year && date.getMonth() === month && date.getDate() === day;
        }
    });
});
