document.addEventListener('DOMContentLoaded', () => {
  const form        = document.querySelector('form');
  const submitBtn   = form.querySelector('button[type="submit"]');
  const inputs = {
    eqAdvers: form.querySelector('input[name="eq_advers"]'),
    lieuMatch: form.querySelector('input[name="lieu_match"]'),
    dateMatch: form.querySelector('input[name="date_match"]'),
    scoreAsbh: form.querySelector('input[name="sc_eq_asbh"]'),
    scoreAdv: form.querySelector('input[name="sc_eq_adv"]'),
    mins: Array.from(form.querySelectorAll('input[name="mins_played[]"]'))
  };

  // utilitaires d’affichage
  function setError(input, msg) {
    input.classList.add('invalid');
    let e = input.parentElement.querySelector('.error-message');
    if (!e) {
      e = document.createElement('div');
      e.className = 'error-message';
      input.parentElement.appendChild(e);
    }
    e.textContent = msg;
  }
  function clearError(input) {
    input.classList.remove('invalid');
    let e = input.parentElement.querySelector('.error-message');
    if (e) e.textContent = '';
  }

  // validate functions
  function validateText(input, maxLen) {
    const v = input.value.trim();
    clearError(input);
    if (v === '') {
      setError(input, 'Ce champ est requis.');
      return false;
    }
    if (v.length > maxLen) {
      setError(input, `Max ${maxLen} caractères.`);
      return false;
    }
    return true;
  }

  function validateDate(input) {
    const v = input.value.trim();
    clearError(input);
    if (!/^\d{4}-\d{2}-\d{2}$/.test(v)) {
      setError(input, 'Format invalide (AAAA-MM-JJ).');
      return false;
    }
    const today = new Date(), [y,m,d] = v.split('-');
    const dt = new Date(+y, m-1, +d);
    if (dt.toString()==='Invalid Date' || dt > today) {
      setError(input, 'Date invalide ou futur.');
      return false;
    }
    return true;
  }

  function validateScore(input) {
    const v = input.value.trim();
    clearError(input);
    if (!/^\d+$/.test(v)) {
      setError(input, 'Entier positif requis.');
      return false;
    }
    return true;
  }

  function validateMins(input) {
    const v = input.value.trim();
    clearError(input);
    if (v === '') {
      input.value = '0';
      return true;
    }
    if (!/^\d+$/.test(v)) {
      setError(input, 'Entier ≥ 0 requis.');
      return false;
    }
    return true;
  }

  // overall form validity
  function validateForm() {
    let ok = true;
    ok = validateText(inputs.eqAdvers, 100)  && ok;
    ok = validateText(inputs.lieuMatch, 50)  && ok;
    ok = validateDate(inputs.dateMatch)     && ok;
    ok = validateScore(inputs.scoreAsbh)    && ok;
    ok = validateScore(inputs.scoreAdv)     && ok;
    inputs.mins.forEach(minInput => {
      ok = validateMins(minInput) && ok;
    });
    submitBtn.disabled = !ok;
  }

  // attach listeners
  inputs.eqAdvers.addEventListener('input', () => validateForm());
  inputs.lieuMatch.addEventListener('input', () => validateForm());
  inputs.dateMatch.addEventListener('change', () => validateForm());
  inputs.scoreAsbh.addEventListener('input', () => validateForm());
  inputs.scoreAdv.addEventListener('input', () => validateForm());
  inputs.mins.forEach(minInput => {
    minInput.addEventListener('input', () => validateForm());
  });

  // initial run
  validateForm();
});
