import 'flowbite';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import $ from 'jquery';

window.$ = window.jQuery = $;
window.Chart = Chart;
Chart.register(ChartDataLabels);