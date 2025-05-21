document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const resultat = document.createElement('div');
    form.appendChild(resultat);

    function afficherErreur(message) {
        resultat.innerHTML = `<p style="backgroung-color:white; color: red; font-weight: bold; text-align: center;">${message}</p>`;
    }

    function afficherSuccès(message) {
        resultat.innerHTML = `<p style="backgroung-color:white; color: green; font-weight: bold; text-align: center;">${message}</p>`;
    }

    function validerFormulaire() {
        const email = form.querySelector('input[name="mail"]');
        const mdp = form.querySelector('input[name="mdp"]');
        let isValid = true;

        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.value.trim() || !regexEmail.test(email.value.trim())) {
            afficherErreur("Adresse e-mail invalide.");
            isValid = false;
        }

        if (!mdp.value.trim()) {
            afficherErreur("Mot de passe requis.");
            isValid = false;
        }

        return isValid;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (!validerFormulaire()) return;

        const formData = new FormData(form);

        fetch('connexion.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            if (data.includes("ok")) {
                afficherSuccès("Connexion réussie !");
                setTimeout(() => window.location.href = '../accueil_staff.html', 1000);
            } else {
                afficherErreur("Erreur : " + data);
            }
        })
        .catch(() => {
            afficherErreur("Erreur serveur ou réseau.");
        });
    });
});
