// Cibler éléments
const modalOverlay = document.getElementById('modalOverlay');
const modalCloseBtn = document.getElementById('modalClose');
const modalName = document.getElementById('modalName');
const modalWeight = document.getElementById('modalWeight');
const modalRPE = document.getElementById('modalRPE');
const modalObservations = document.getElementById('modalObservations');

// Ouvrir modal avec données
document.querySelectorAll('.player-card').forEach(card => {
  card.addEventListener('click', () => {
    const data = JSON.parse(card.getAttribute('data-player'));
    modalName.textContent = data.nom;
    modalWeight.textContent = data.poids;
    modalRPE.textContent = data.rpe;
    modalObservations.textContent = data.observations;

    modalOverlay.classList.add('active');
  });
});

// Fermer modal au clic sur bouton ou en cliquant sur l'overlay (hors modal)
modalCloseBtn.addEventListener('click', () => {
  modalOverlay.classList.remove('active');
});
modalOverlay.addEventListener('click', (e) => {
  if(e.target === modalOverlay) {
    modalOverlay.classList.remove('active');
  }
});
