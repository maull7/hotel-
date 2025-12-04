const ctx1 = document.getElementById('chart1');
const ctx2 = document.getElementById('chart2');

new Chart(ctx1, {
  type: 'line',
  data: {
    labels: ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"],
    datasets: [{
      label: 'Okupansi (%)',
      data: [70, 75, 80, 78, 82, 88, 90],
      borderColor: "#3498db",
      borderWidth: 3,
      fill: false
    }]
  }
});

new Chart(ctx2, {
  type: 'bar',
  data: {
    labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun"],
    datasets: [{
      label: 'Pendapatan (juta)',
      data: [120, 135, 140, 150, 160, 170],
      backgroundColor: "#2ecc71"
    }]
  }
});
