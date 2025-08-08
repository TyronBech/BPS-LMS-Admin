import 'flowbite';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';

window.Chart = Chart;
Chart.register(ChartDataLabels);