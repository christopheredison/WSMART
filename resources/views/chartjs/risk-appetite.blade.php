<script>
document.addEventListener("DOMContentLoaded", function() {
  Chart.defaults.font.family = "InterVariable";
  var ctx = document.getElementById('riskAppetiteChart').getContext('2d');
  var riskAppetiteChart = new Chart(ctx, {
    type: 'bar', // Use 'bar' and set indexAxis to 'y' for horizontal bars
    data: {
      labels: ['Konservatif', 'Moderat', 'Agresif'],
      datasets: [{
        label: '% Risk Appetite',
        data: [
          {{ $data->persentase_risk_appetite_konservatif ?? NULL }},
          {{ $data->persentase_risk_appetite_moderat ?? NULL }},
          {{ $data->persentase_risk_appetite_agresif ?? NULL }}
        ],
        backgroundColor: [
          'rgba(184, 217, 53, 0.2)',
          'rgba(79, 201, 218, 0.2)',
          'rgba(240, 100, 69, 0.2)'
        ],
        borderColor: [
          'rgba(184, 217, 53, 1)',
          'rgba(79, 201, 218, 1)',
          'rgba(240, 100, 69, 1)'
        ],
        borderWidth: 1
      }]
    },
    options: {
      indexAxis: 'y', // This makes the bars horizontal
      scales: {
        x: {
          beginAtZero: true,
          max: 100
        },
        y: {
          grid: {
            display: false
          }
        }
      },
      plugins: {
        legend: {
          display: false,
        }
      },
      aspectRatio: 3.75,
      responsive: true,
    }
  });
});
</script>
