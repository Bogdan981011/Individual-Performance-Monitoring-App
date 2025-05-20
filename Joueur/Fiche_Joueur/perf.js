const dataLabels = dernieres_mesures.dates;

const createLineChart = (id, label, data, borderColor, bgColor) => {
    const ctx = document.getElementById(id).getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dataLabels,
            datasets: [{
                label: label,
                data: data,
                borderColor: borderColor,
                backgroundColor: bgColor,
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
                    beginAtZero: false
                }
            }
        }
    });
};

// Créer chaque graphique séparément
createLineChart('graph-poids', 'Poids (kg)', dernieres_mesures.poids, 'blue', 'lightblue');
createLineChart('graph-taille', 'Taille (m)', dernieres_mesures.taille, 'green', 'lightgreen');
createLineChart('graph-img', 'IMG (%)', dernieres_mesures.img, 'red', 'pink');
