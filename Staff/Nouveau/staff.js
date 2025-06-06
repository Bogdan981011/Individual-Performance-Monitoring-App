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
                <label>Poste :</label>
                <select name="poste">
                    <option value="" disabled selected>Sélectionner son rôle</option>
                    <option value="coach">Coach</option>
                    <option value="manager">Manager</option>
                    <option value="directeur">Directeur</option>
                    <option value="pp">Prépa physique</option>
                    <option value="kine">Kiné</option>
                    <option value="Analyste vidéo">Analyste vidéo</option>
                    <option value="admin">Corps admin</option>
                    <option value="pm">Prépa mental</option>
                </select>
                <span class="error-message"></span>
            </p>
            <p>
                <label>Nom :</label>
                <input type="text" name="nom" ><span class="error-message"></span>
            </p>
            <p>
                <label>Prénom :</label>
                <input type="text" name="prénom"><span class="error-message"></span>
            </p>
            <p>
                <label>Adresse e-mail :</label>
                <input type="email" name="mail"><span class="error-message"></span>
            </p>
            <p>
                <label>Mot de passe provisoire :</label>
                <input type="text" name="mdp"><span class="error-message"></span>
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
            const prénom = personne.querySelector('input[name="prénom"]');
            const email = personne.querySelector('input[name="mail"]');
            const poste = personne.querySelector('select[name="poste"]');
            const mdp = personne.querySelector('input[name="mdp"]');
            
            // Vérifier que tous les champs sont remplis
            if (!nom.value.trim()) {
                showError(nom, 'Le nom est obligatoire.');
                isValid = false;
            } else {
                clearError(nom);
            }

            if (!prénom.value.trim()) {
                showError(prénom, 'Le prénom est obligatoire.');
                isValid = false;
            } else {
                clearError(prénom);
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

            if (!poste.value) {
                showError(poste, 'Le rôle est obligatoire.');
                isValid = false;
            } else {
                clearError(poste);
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
        const staffs = [];
        const personnes = document.querySelectorAll('.personne');
        const csrfToken = form.querySelector('input[name="csrf_token"]').value;

        personnes.forEach((personne) => {
            const nom = personne.querySelector('input[name="nom"]').value.trim();
            const prénom = personne.querySelector('input[name="prénom"]').value.trim();
            const email = personne.querySelector('input[name="mail"]').value.trim();
            const poste = personne.querySelector('select[name="poste"]').value;
            const mdp = personne.querySelector('input[name="mdp"]').value.trim();

            staffs.push({
                nom,
                prénom,
                email,
                poste, 
                mdp
            });
        });

        fetch('save_staff.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
           
            body: JSON.stringify({ 
                staffs: staffs, 
                csrf_token: csrfToken  
            })
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes("ok")) {
                afficherSuccès("Réponses envoyées avec succès.");
                setTimeout(() => {
                    window.location.href = `/vizia/Staff/Nouveau/creer.html`;
                }, 1000);
            } else {
                afficherErreur("Erreur serveur : " + data);
            }
        })
        .catch(() => {
            afficherErreur("Erreur serveur ou réseau.");
        });

    });

});
