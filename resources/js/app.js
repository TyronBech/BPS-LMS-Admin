import 'flowbite';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import $ from 'jquery';
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.$ = window.jQuery = $;
window.Chart = Chart;
Chart.register(ChartDataLabels);