document.addEventListener('DOMContentLoaded', function () {
    
    // Fonction pour ajouter une section "personne"
    function ajouterPersonne() {
        const container = document.getElementById('personnes');
        const index = container.children.length + 1;
        const personne = document.createElement('div');
        personne.className = 'form-container personne';

        personne.innerHTML = `
            <button type="button" class="supprimer" onclick="this.parentElement.remove()">− Supprimer</button>
            <h2>Personne ${index}</h2>
            <p>
                <label>Equipe :</label>
                <select name="equipe">
                    <option value="" disabled selected>Sélectionner son équipe</option>
                    <option value="crabos">CRABOS</option>
                    <option value="cadets a">CADETS A</option>
                    <option value="cadets b">CADETS B</option>
                    <option value="espoirs">ESPOIRS</option>
                </select>
                <span class="error-message"></span>
            </p>
            <p>
                <label for="annee">Date de naissance :</label>
                <input type="date" name="annee" id="annee"><span class="error-message"></span>
            </p>
            <p>
                <label>Nom :</label>
                <input type="text" name="nom"><span class="error-message"></span>
            </p>
            <p>
                <label>Prénom :</label>
                <input type="text" name="prenom"><span class="error-message"></span>
            </p>
            <p>
                <label for="poste">Poste :</label>
                <input type="text" name="poste" id="poste"><span class="error-message"></span>
            </p>
            <p>
                <label>Adresse e-mail :</label>
                <input type="email" name="mail"><span class="error-message"></span>
            </p>
            <p>                    
                <label>Mot de passe provisoire :</label>
                <input type="text" name="mdp"><span class="error-message"></span>
            </p>
            <p>
                <label>Photo :</label>
                <input type="file" name="photo" accept="image/png,image/jpeg,image/webp">
                <span class="error-message"></span>
            </p>

    
        `;
        container.appendChild(personne);
    }

    
    function showError(input, message) {
        const errorDiv = input.parentElement.querySelector(".error-message");
        errorDiv.textContent = message;
    }

    // Fonction pour effacer le message d'erreur
    function clearError(input) {
        const errorDiv = input.parentElement.querySelector(".error-message");
        errorDiv.textContent = "";
    }

    function afficherErreur(message) {
        resultat.innerHTML = `
            <p style="background-color: white; color: red; text-align: center; font-weight: bold; border: 1px solid red">${message}</p>
        `;
    }
        
    // Fonction pour afficher le succès
    function afficherSuccès(message) {
        resultat.innerHTML = `<p style="color: green; text-align: center; font-weight: bold; border: 1px solid green">${message}</p>`;
    }

    function dateOk(dateString, input) {
        const dateParts = dateString.split('-');
        const day = parseInt(dateParts[2], 10);
        const month = parseInt(dateParts[1], 10) - 1; // Les mois sont indexés à partir de 0
        const year = parseInt(dateParts[0], 10);

        const annee = new Date(year, month, day);
        const valid = annee.getDate() === day && annee.getMonth() === month && annee.getFullYear() === year;
        if (valid) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (annee > today) {
                showError(input, "L'année ne peut pas être dans le futur.");
                return false;
            }
        } else {
            showError(input, "Le format de la date est invalide.");
            return false;
        }
        clearError(input);
        return true;
    }

    // Fonction pour valider l'email
    function validerEmail(input) {
        const email = input.value.trim();
        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!regexEmail.test(email)) {
            showError(input, 'Format d\'email invalide.');
        } else {
            clearError(input);
        }
    }

    // Fonction pour attacher les écouteurs de validation à un bloc .personne
    function attacherValidation(personne) {        
        const dateInput = personne.querySelector('input[name="annee"]');
        dateInput.addEventListener('input', () => dateOk(dateInput.value, dateInput));

        const emailInput = personne.querySelector('input[name="mail"]');
        emailInput.addEventListener('input', () => validerEmail(emailInput));
    }

    // Attacher la validation à la première personne déjà présente
    document.querySelectorAll('.personne').forEach(attacherValidation);

    // Quand on ajoute une nouvelle personne, on attache aussi la validation
    const btnAjouter = document.querySelector('.ajouter');
    btnAjouter.addEventListener('click', function () {
        ajouterPersonne();
        // Attacher validation à la dernière personne ajoutée
        const dernier = document.querySelectorAll('.personne');
        attacherValidation(dernier[dernier.length - 1]);
    });

    
    // Fonction de validation du formulaire
    function validerFormulaire() {
        let isValid = true;
        const personnes = document.querySelectorAll('.personne');
        
        personnes.forEach((personne) => {
            const nom = personne.querySelector('input[name="nom"]');
            const prenom = personne.querySelector('input[name="prenom"]');
            const email = personne.querySelector('input[name="mail"]');
            const equipe = personne.querySelector('select[name="equipe"]');
            const mdp = personne.querySelector('input[name="mdp"]');
            const poste = personne.querySelector('input[name="poste"]');
            const annee = personne.querySelector('input[name="annee"]');
            
            // Vérifier que tous les champs sont remplis
            if (!nom.value.trim()) {
                showError(nom, 'Le nom est obligatoire.');
                isValid = false;
            } else {
                clearError(nom);
            }

            if (!prenom.value.trim()) {
                showError(prenom, 'Le prénom est obligatoire.');
                isValid = false;
            } else {
                clearError(prenom);
            }

            if (!poste.value.trim()) {
                showError(poste, 'Le poste est obligatoire.');
                isValid = false;
            } else {
                clearError(poste);
            }

            if (!email.value.trim()) {
                showError(email, 'L\'adresse e-mail est obligatoire.');
                isValid = false;
            } else {
                validerEmail(email);
                if (email.parentElement.querySelector(".error-message").textContent) {
                    isValid = false;
                }
            }

            if (!annee.value.trim()) {
                showError(annee, 'L\'année est obligatoire.');
                isValid = false;
            } else {
                dateOk(annee.value, annee);
                if (annee.parentElement.querySelector(".error-message").textContent) {
                    isValid = false;
                }
            }

            if (!equipe.value) {
                showError(equipe, 'Le rôle est obligatoire.');
                isValid = false;
            } else {
                clearError(equipe);
            }

            if (!mdp.value.trim()) {
                showError(mdp, 'Le mot de passe est obligatoire.');
                isValid = false;
            } else {
                clearError(mdp);
            }
        });

        return isValid;
    }


    // Soumission du formulaire avec AJAX
    const form = document.querySelector('form');
    const resultat = document.createElement('div');
    form.appendChild(resultat)

    form.addEventListener('submit', function (event) {
        event.preventDefault(); // Empêcher la soumission classique du formulaire

        // Validation des champs
        if (!validerFormulaire()) {
            afficherErreur("Veuillez remplir tous les champs sans erreurs.");
            return;
        }

        // Si tout est OK, on envoie les données par AJAX
        const joueurs = [];
        const fichiers = []
        const personnes = document.querySelectorAll('.personne');
        const csrfToken = form.querySelector('input[name="csrf_token"]').value;

        personnes.forEach((personne) => {
            const nom = personne.querySelector('input[name="nom"]').value.trim();
            const prenom = personne.querySelector('input[name="prenom"]').value.trim();
            const email = personne.querySelector('input[name="mail"]').value.trim();
            const equipe = personne.querySelector('select[name="equipe"]').value;
            const mdp = personne.querySelector('input[name="mdp"]').value.trim();
            const annee = personne.querySelector('input[name="annee"]').value.trim();
            const poste = personne.querySelector('input[name="poste"]').value.trim();
            
            joueurs.push({
                nom,
                prenom,
                email,
                equipe, 
                mdp,
                annee,
                poste
            });
            
            const fileInput = personne.querySelector('input[type="file"]');
            fichiers.push(fileInput.files[0] || null);
        });

        fetch('save_joueur.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
           
            body: JSON.stringify({ 
                joueurs: joueurs, 
                csrf_token: csrfToken  
            })
        })
        .then(res  => res.json())
        .then(async response  => {
            if (response.status === "ok") {
                const ids = response.ids;
                const formData = new FormData();

                ids.forEach((id, i) => {
                    if (fichiers[i]) {
                        formData.append('photo[]', fichiers[i]);
                        formData.append('id_joueur[]', id);
                    }
                });

                const uploadResponse = await fetch('save_image.php', {
                    method: 'POST',
                    body: formData
                });

                if (uploadResponse.ok) {
                    afficherSuccès("Joueurs créés avec succès !");
                    setTimeout(() => {
                        window.location.href = `/vizia/Staff/Nouveau/creer.php`;
                    }, 1000);
                } else {
                    afficherErreur("Erreur lors de l'upload des fichiers.");
                }
            } else {
                afficherErreur("Erreur serveur : " + data);
            }
        })
        .catch(() => {
            afficherErreur("Erreur serveur ou réseau.");
        });

    });

});
