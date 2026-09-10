import './bootstrap';

import Alpine from 'alpinejs';
import { marked } from 'marked';
import Chart from 'chart.js/auto';
import { renderRagMarkdown } from './rag-markdown';

window.Alpine = Alpine;
window.marked = marked;
window.Chart = Chart;
window.renderRagMarkdown = renderRagMarkdown;

Alpine.start();
