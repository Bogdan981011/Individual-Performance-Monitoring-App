document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const resultat = document.createElement('div');
    form.appendChild(resultat);

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const type = form['type-entrainement'].value;
        const rpe = form['difficulte']?.value;
        const commentaire = form['observations']?.value || '';
        const temps = form['temps-entrainement'].value;
        const date = form['date'].value; // Ajouté ici

        const params = new URLSearchParams(window.location.search);
        const idJoueur = params.get('id');
        const csrfToken = form.querySelector('input[name="csrf_token"]')?.value;


        if (!type || !rpe || !date) {
            return afficherErreur("Tous les champs obligatoires doivent être remplis.");
        }

        const rpeInt = parseInt(rpe, 10);
        if (isNaN(rpeInt) || rpeInt < 1 || rpeInt > 10) {
            return afficherErreur("La valeur RPE doit être un nombre entre 1 et 10.");
        }

        if (commentaire.length > 500) {
            return afficherErreur("Le commentaire ne doit pas dépasser 500 caractères.");
        }

        if (!dateOk(date)) {
            return afficherErreur("Le format de la date est invalide.");
        }

        const dateEntree = new Date(date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (dateEntree > today) {
            return afficherErreur("La date ne peut pas être dans le futur.");
        }

        const formData = new FormData();
        formData.append('date', date);
        formData.append('type_entrainement', type);
        formData.append('difficulte', rpe);
        formData.append('observations', commentaire);
        formData.append('id_joueur', idJoueur);
        formData.append('temps_entrainement', temps);
        formData.append('csrf_token', csrfToken); // assure-toi que cette variable existe quelque part

        fetch('save_rpe.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes("ok")) {
                afficherSucces("Réponse enregistrée avec succès !");
                setTimeout(() => {
                    window.location.href = 'formulaires.html';
                }, 1500);
            } else {
                afficherErreur("Erreur serveur : " + data);
            }
        })
        .catch(() => {
            afficherErreur("Erreur de connexion.");
        });

        function afficherErreur(msg) {
            resultat.innerHTML = `<p style="color: white; background: red; padding: 5px; text-align:center;">${msg}</p>`;
        }

        function afficherSucces(msg) {
            resultat.innerHTML = `<p style="color: green; text-align:center;">${msg}</p>`;
        }

        function dateOk(dateString) {
            const dateParts = dateString.split('-');
            if (dateParts.length !== 3) return false;

            const year = parseInt(dateParts[0], 10);
            const month = parseInt(dateParts[1], 10) - 1;
            const day = parseInt(dateParts[2], 10);

            const date = new Date(year, month, day);
            return date.getFullYear() === year &&
                   date.getMonth() === month &&
                   date.getDate() === day;
        }
    });
});
