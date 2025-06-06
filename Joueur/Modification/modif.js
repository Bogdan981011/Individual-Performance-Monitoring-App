document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const resultat = document.createElement('div');
    form.appendChild(resultat);

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const mdpAv = form.mdp_av.value.trim();
        const newMdp = form.mdp.value.trim();
        const confirmMdp = form.confirmation_mdp.value.trim();
        const csrfToken = form.csrf_token.value;

        // Vérification des champs vides
        if (!mdpAv || !newMdp || !confirmMdp) {
            return afficherErreur("Tous les champs sont requis.");
        }

        // Vérification de la correspondance
        if (newMdp !== confirmMdp) {
            return afficherErreur("Les nouveaux mots de passe ne correspondent pas.");
        }

        // Préparation des données
        const formData = new FormData();
        formData.append('mdp_av', mdpAv);
        formData.append('new_mdp', newMdp);
        formData.append('confirmMdp', confirmMdp);
        formData.append('csrf_token', csrfToken);

        // Envoi AJAX
        fetch('save_modif.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            if (data.includes("ok")) {
                afficherSucces("Mot de passe modifié avec succès.");
                setTimeout(() => { window.location.href =`../accueil_joueur.html`}, 1000);
            } else {
                afficherErreur("Erreur : " + data);
            }
        })
        .catch(() => {
            afficherErreur("Erreur serveur ou réseau.");
        });

        // Affichage d'erreur
        function afficherErreur(msg) {
            resultat.innerHTML = `<p style="color: red;">${msg}</p>`;
        }

        // Affichage succès
        function afficherSucces(msg) {
            resultat.innerHTML = `<p style="color: green;">${msg}</p>`;
        }
    });
});
