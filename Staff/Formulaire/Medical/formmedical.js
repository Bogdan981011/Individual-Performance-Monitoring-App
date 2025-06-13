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
        const recommandation = form.recommandation.value.trim();
        const reprise = form.reprise.value.trim();

        const params = new URLSearchParams(window.location.search);
        const idJoueur = params.get('id');
        const equipe = params.get('eq');
        const csrfToken = form.querySelector('input[name="csrf_token"]').value;

        // Validation des champs
        const graviteOk = /^\d+$/.test(gravite) && gravite >= 1 && gravite <= 10;
        const champsNonVides = type && gravite && date;

        // Vérification des champs vides (excepté recommandation/reprise)
        if (!champsNonVides) {
            return afficherErreur("Tous les champs sauf recommandation / reprise doivent être remplis.");
        }

        // Validation format date
        if (!dateOk(date)) {
            return afficherErreur("Le format de la date est invalide.");
        }

        // Validation date (doit être au plus tard aujourd'hui)
        const dateEntree = new Date(date.split('/').reverse().join('/')); // Convertit la date du format JJ/MM/AAAA en objet Date
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Réinitialiser l'heure de "today" à minuit

        // Valisation de la longueur recommandation
        if (recommandation.length > 500) {
            return afficherErreur("La recommandation ne doit pas dépasser 500 caractères.");
        }

        
        // Valisation de la longueur reprise
        if (reprise.length > 500) {
            return afficherErreur("La reprise ne doit pas dépasser 500 caractères.");
        }

        if (dateEntree > today) {
            return afficherErreur("La date ne peut pas être dans le futur.");
        }

        // Validation gravité
        if (!graviteOk) {
            return afficherErreur("La gravité doit être un entier.");
        }

        // Si tout est OK, on envoie les données par AJAX
        const formData = new FormData();
        formData.append('type', type);
        formData.append('gravite', gravite);
        formData.append('date', date);
        formData.append('recommandation', recommandation);
        formData.append('reprise', reprise)
        formData.append('id_joueur', idJoueur); // récupère l'ID de l'URL
        formData.append('csrf_token', csrfToken);

        fetch('save_formmedical.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log("Réponse PHP :", data); // Tu verras ici l’erreur ou "OK"
            if (data.includes("ok")) {
                afficherSuccès("Réponses envoyées avec succès.");
                setTimeout(() => {
                    if (equipe === "A") {
                        window.location.href = `/vizia/Staff/Equipe/CadetA/joueurs_cadetA.php`;
                    } else if (equipe === "B") {
                        window.location.href = `/vizia/Staff/Equipe/CadetB/joueurs_cadetB.php`;
                    } else if (equipe === "C") {
                        window.location.href = `/vizia/Staff/Equipe/Crabos/joueurs_crabos.php`;
                    }else if (equipe === "E") {
                        window.location.href = `/vizia/Staff/Equipe/Espoirs/joueurs_espoirs.php`;
                    } else {
                        window.location.href = `/vizia/Staff/accueil_staff.php`;
                    }
                }, 1000); // Fermer setTimeout ici
            } else {
                afficherErreur("Erreur serveur : " + data);
            }
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
            return date.getDate() === day && date.getMonth() === month && date.getFullYear() === year;
        }
    });
});
