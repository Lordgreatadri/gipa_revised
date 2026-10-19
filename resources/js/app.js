import './bootstrap';

import Alpine from '@alpinejs/csp';
import focus from '@alpinejs/focus';

import { registerCountryPicker } from './country-picker';
import { renderIcons } from './icons';
import { registerThemeStore } from './theme';
import { registerUi } from './ui';
import './assistant';
import './pwa';

Alpine.plugin(focus);
registerThemeStore(Alpine);
registerCountryPicker(Alpine);
registerUi(Alpine);

window.Alpine = Alpine;
Alpine.start();

renderIcons();

if (document.getElementById('opportunity-chart-data') || document.getElementById('reference-chart-data') || document.getElementById('dashboard-chart-data')) {
    import('./charts').then(({ mountOpportunityCharts, mountReferenceChart, mountDashboardCharts }) => {
        mountOpportunityCharts();
        mountReferenceChart();
        mountDashboardCharts();
    });
}

if (document.getElementById('investment-map')) {
    import('./map').then(({ mountInvestmentMap }) => {
        mountInvestmentMap();
    });
}
