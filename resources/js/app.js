import './bootstrap';
import 'flowbite';
import Chart from 'chart.js/auto';
import Alpine from 'alpinejs';
import ChartDataLabels from 'chartjs-plugin-datalabels';

window.Chart = Chart;
window.Alpine = Alpine;
Chart.register(ChartDataLabels);
Alpine.start();
