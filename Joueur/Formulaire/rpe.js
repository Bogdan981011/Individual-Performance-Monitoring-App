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



        const params = new URLSearchParams(window.location.search);
        const idJoueur = params.get('id');

        if (!type || !rpe) {
            return afficherErreur("Tous les champs obligatoires doivent être remplis.");
        }

        const rpeInt = parseInt(rpe, 10);
        if (isNaN(rpeInt) || rpeInt < 1 || rpeInt > 10) {
            return afficherErreur("La valeur RPE doit être un nombre entre 1 et 10.");
        }

        if (commentaire.length > 500) {
            return afficherErreur("Le commentaire ne doit pas dépasser 500 caractères.");
        }

        const formData = new FormData();
        formData.append('type_entrainement', type);
        formData.append('difficulte', rpe);
        formData.append('observations', commentaire);
        formData.append('id_joueur', idJoueur);
        formData.append('temps_entrainement', temps);
        formData.append('csrf_token', csrfToken);


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
    });
});
