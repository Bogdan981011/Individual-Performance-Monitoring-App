document.addEventListener('DOMContentLoaded', () => {
  console.log("[Debug] DOM loaded");

  if (!window.data_graph || data_graph.length === 0) {
    console.warn("[Debug] data_graph is undefined or empty");
    return;
  }

  console.log("[Debug] Raw data_graph:", data_graph);

  // Step 1: Build a set of all unique dates and test types
  const allDatesSet = new Set();
  const groupedData = {};

  data_graph.forEach(entry => {
    const type = entry.type_test;
    const date = entry.date_test.split(' ')[0];
    const value = parseFloat(entry.mesure_test);

    allDatesSet.add(date);
    if (!groupedData[type]) groupedData[type] = {};
    groupedData[type][date] = value;
  });

  const sortedDates = Array.from(allDatesSet).sort();

  // Step 2: Prepare datasets for Chart.js
  const datasets = Object.entries(groupedData).map(([type, valuesByDate], index) => {
    const dataPoints = sortedDates.map(date => valuesByDate[date] ?? null);

    return {
        label: `Test ${type}`,
        data: dataPoints,
        fill: false,
        tension: 0.2,
        borderWidth: 2,
        spanGaps: true,
        hidden: index !== 0  // ✅ hide all except the first
    };
  });


  console.log("[Debug] Final datasets:", datasets);

  // Step 3: Create canvas and render chart
  const container = document.getElementById('graph-tests-container');
  if (!container) {
    console.error("[Debug] Could not find container with id 'graph-tests-container'");
    return;
  }

  const canvas = document.createElement('canvas');
  container.appendChild(canvas);

  new Chart(canvas, {
  type: 'line',
  data: {
    labels: sortedDates,
    datasets: datasets
  },
  options: {
    responsive: true,
    maintainAspectRation: true,
    plugins: {
      title: {
        display: true,
        text: 'Évolution de tous les tests physiques'
      },
      legend: {
        display: true,
        position: 'top',
        onClick: (e, legendItem, legend) => {
          const index = legendItem.datasetIndex;
          const chart = legend.chart;

          // Hide all except the clicked one
          chart.data.datasets.forEach((ds, i) => {
            chart.getDatasetMeta(i).hidden = i !== index;
          });

          chart.update();
        }
      }
    }, // ← ✅ this was missing
    scales: {
      y: {
        beginAtZero: true
      }
    }
  }
});

});
