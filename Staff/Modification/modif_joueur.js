document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const resultat = document.createElement('div');
    form.appendChild(resultat);  // Ajoute la zone d'erreur ou de succès sous le formulaire

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Empêche l'envoi du formulaire classique

        // Récupérer les valeurs des champs
        const nom = form.nom.value.trim() || form.nom.placeholder.trim();
        const prenom = form.prenom.value.trim() || form.prenom.placeholder.trim() ;
        const poste = form.poste.value.trim() || form.poste.placeholder.trim();
        const email = form.email.value.trim() || form.email.placeholder.trim();
        const mdp = form.mdp.value.trim();
        const annee = form.annee.value.trim() || form.annee.value.trim();
        const nom_equipe = form.nom_equipe.value;

        const params = new URLSearchParams(window.location.search);
        const idJoueur = params.get('id');
        const equipe = params.get('eq');
        const csrfToken = form.querySelector('input[name="csrf_token"]').value;        
        const fileInput = form.querySelector('input[type="file"]');
        

        // Validation email
        if (email && !emailOk(email)) {
            return afficherErreur("Le format de l'email est invalide.");
        }
        
        // Validation format date
        if (annee && !dateOk(annee)) {
            return afficherErreur("Le format de l'année est invalide.");
        }

        // Validation année (doit être au plus tard aujourd'hui)
        if (annee) {
            const dateEntree = new Date(annee.split('/').reverse().join('/')); // Convertit l'année du format JJ/MM/AAAA en objet Date
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (dateEntree > today) {
                return afficherErreur("L'année ne peut pas être dans le futur.");
            }
        }

        // Fonction de validation de la date
        function dateOk(dateString) {
            const dateParts = dateString.split('-');
            const day = parseInt(dateParts[2], 10);
            const month = parseInt(dateParts[1], 10) - 1; // Les mois sont indexés à partir de 0
            const year = parseInt(dateParts[0], 10);

            const annee = new Date(year, month, day);
            return annee.getDate() === day && annee.getMonth() === month && annee.getFullYear() === year;
        }
        
        // Si tout est OK, on envoie les données par AJAX
        const formData = new FormData();
        formData.append('nom', nom );
        formData.append('prenom', prenom);
        formData.append('poste', poste);
        formData.append('email', email);
        formData.append('mdp', mdp);
        formData.append('annee', annee); 
        formData.append('nom_equipe', nom_equipe);
        formData.append('id_joueur', idJoueur); // récupère l'ID de l'URL
        formData.append('csrf_token', csrfToken);

        fetch('save_modif_joueur.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(async data => {
            if (data.includes("ok")) {
                const formDataPhoto = new FormData();
                
                if (fileInput && fileInput.files.length > 0) {
                    formDataPhoto.append('photo', fileInput.files[0]);
                    formDataPhoto.append('photo', fileInput);
                    formDataPhoto.append('id_joueur', idJoueur);

                    const uploadResponse = await fetch('change_image.php', {
                        method: 'POST',
                        body: formDataPhoto
                    });

                    if (uploadResponse.ok) {
                        afficherSuccès("Modifications enregistrées avec succès.");
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
                        }, 1000); // Redirection après succès
                    } else {
                        afficherErreur("Erreur lors de l'upload des fichiers.");
                    }
                } else {
                    afficherSuccès("Modifications enregistrées avec succès.");
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
                        }, 1000); // Redirection après succès
                }
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
                <p style="color: red; text-align: center; font-weight: bold; border: 1px solid white">${message}</p>`;
        }

        // Fonction pour afficher le succès
        function afficherSuccès(message) {
            resultat.innerHTML = `<p style="color: green;">${message}</p>`;
        }

        // Fonction pour valider l'email (simple regex)
        function emailOk(emailString) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(emailString);
        }
    });
});
