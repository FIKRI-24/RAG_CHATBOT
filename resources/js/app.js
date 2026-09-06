import './bootstrap';

import Alpine from 'alpinejs';
import { marked } from 'marked';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.marked = marked;
window.Chart = Chart;

Alpine.start();
