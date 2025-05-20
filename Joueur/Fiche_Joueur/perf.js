// Fonction pour créer un graphique en ligne
const createLineChart = (id, label, data, borderColor, bgColor, labels) => {
    const ctx = document.getElementById(id).getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,  // ⚠️ Les dates spécifiques à cette mesure
            datasets: [{
                label: label,
                data: data,
                borderColor: borderColor,
                backgroundColor: bgColor,
                fill: false,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: label
                }
            },
            scales: {
                x: {
                    title: { display: true, text: 'Date' }
                },
                y: {
                    beginAtZero: false,
                    title: { display: true, text: label }
                }
            }
        }
    });
};

// Appel des graphiques avec les dates et valeurs propres à chaque mesure
createLineChart(
    'graph-poids',
    'Poids (kg)',
    dernieres_mesures.poids.valeurs,
    'blue',
    'lightblue',
    dernieres_mesures.poids.dates
);

createLineChart(
    'graph-taille',
    'Taille (m)',
    dernieres_mesures.taille.valeurs,
    'green',
    'lightgreen',
    dernieres_mesures.taille.dates
);

createLineChart(
    'graph-img',
    'IMG (%)',
    dernieres_mesures.img.valeurs,
    'red',
    'pink',
    dernieres_mesures.img.dates
);
