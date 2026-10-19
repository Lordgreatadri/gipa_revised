import { BarChart, LineChart, PieChart } from 'echarts/charts';
import { GridComponent, LegendComponent, TooltipComponent } from 'echarts/components';
import { init, use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';

use([BarChart, LineChart, PieChart, GridComponent, LegendComponent, TooltipComponent, CanvasRenderer]);

const palette = ['#0f6e56', '#d4a017', '#0369a1', '#4f46e5', '#0d9488', '#e11d48', '#b45309', '#334155'];
const regionalColors = {
    draft: '#d97706',
    approved: '#4f46e5',
    active: '#059669',
    cancelled: '#e11d48',
};

export function mountOpportunityCharts() {
    const source = document.getElementById('opportunity-chart-data');

    if (!source) {
        return;
    }

    let charts;

    try {
        charts = JSON.parse(source.textContent || '{}');
    } catch {
        return;
    }

    const mounted = [];
    let quarter = String(charts.regional?.current || 1);

    const theme = () => {
        const dark = document.documentElement.classList.contains('dark');

        return {
            text: dark ? '#e2e8f0' : '#0f172a',
            muted: dark ? '#cbd5e1' : '#64748b',
            split: dark ? '#334155' : '#e2e8f0',
        };
    };

    const bar = (elementId, seriesName, data) => {
        const node = document.getElementById(elementId);

        if (!node || !data) {
            return null;
        }

        const chart = init(node);
        mounted.push({
            chart,
            draw() {
                const colors = theme();
                chart.setOption({
                    backgroundColor: 'transparent',
                    color: palette,
                    tooltip: { trigger: 'axis' },
                    grid: { left: 40, right: 16, top: 24, bottom: 72 },
                    xAxis: {
                        type: 'category',
                        data: data.labels || [],
                        axisLabel: { color: colors.muted, interval: 0, rotate: 28 },
                        axisLine: { lineStyle: { color: colors.split } },
                    },
                    yAxis: {
                        type: 'value',
                        minInterval: 1,
                        axisLabel: { color: colors.muted },
                        splitLine: { lineStyle: { color: colors.split } },
                    },
                    series: [{ name: seriesName, type: 'bar', data: data.values || [], barMaxWidth: 36 }],
                });
            },
        });

        return chart;
    };

    bar('chart-sectors', 'Opportunities', charts.sectors);
    bar('chart-regions', 'Opportunities', charts.regions);

    const pieNode = document.getElementById('chart-states');

    if (pieNode && charts.states) {
        const pie = init(pieNode);
        mounted.push({
            chart: pie,
            draw() {
                const colors = theme();
                pie.setOption({
                    backgroundColor: 'transparent',
                    color: palette,
                    tooltip: { trigger: 'item' },
                    legend: { show: false },
                    series: [{
                        type: 'pie',
                        radius: ['36%', '58%'],
                        center: ['50%', '50%'],
                        label: {
                            show: true,
                            color: colors.text,
                            fontSize: 11,
                            formatter: '{b}\n{c}',
                        },
                        labelLine: {
                            show: true,
                            length: 8,
                            length2: 6,
                            lineStyle: { color: colors.muted },
                        },
                        data: (charts.states.labels || []).map((name, index) => ({
                            name,
                            value: (charts.states.values || [])[index] || 0,
                        })),
                    }],
                });
            },
        });
    }

    const lineNode = document.getElementById('chart-trend');

    if (lineNode && charts.trend) {
        const line = init(lineNode);
        mounted.push({
            chart: line,
            draw() {
                const colors = theme();
                line.setOption({
                    backgroundColor: 'transparent',
                    color: ['#0f6e56'],
                    tooltip: { trigger: 'axis' },
                    grid: { left: 40, right: 16, top: 24, bottom: 32 },
                    xAxis: {
                        type: 'category',
                        data: charts.trend.labels || [],
                        axisLabel: { color: colors.muted },
                        axisLine: { lineStyle: { color: colors.split } },
                    },
                    yAxis: {
                        type: 'value',
                        minInterval: 1,
                        axisLabel: { color: colors.muted },
                        splitLine: { lineStyle: { color: colors.split } },
                    },
                    series: [{
                        name: 'Entries',
                        type: 'line',
                        smooth: true,
                        data: charts.trend.values || [],
                        areaStyle: { color: 'rgba(15, 110, 86, 0.18)' },
                    }],
                });
            },
        });
    }

    const regionalNode = document.getElementById('chart-regional');
    const buttons = Array.from(document.querySelectorAll('[data-quarter]'));

    if (regionalNode && charts.regional) {
        const regional = init(regionalNode);
        const drawRegional = () => {
            const colors = theme();
            const pack = charts.regional.quarters?.[quarter] || {};
            regional.setOption({
                backgroundColor: 'transparent',
                color: [regionalColors.draft, regionalColors.approved, regionalColors.active, regionalColors.cancelled],
                tooltip: { trigger: 'axis' },
                legend: { bottom: 0, textStyle: { color: colors.text } },
                grid: { left: 48, right: 16, top: 28, bottom: 96 },
                xAxis: {
                    type: 'category',
                    data: charts.regional.regions || [],
                    axisLabel: { color: colors.muted, interval: 0, rotate: 32 },
                    axisLine: { lineStyle: { color: colors.split } },
                },
                yAxis: {
                    type: 'value',
                    minInterval: 1,
                    axisLabel: { color: colors.muted },
                    splitLine: { lineStyle: { color: colors.split } },
                },
                series: [
                    { name: 'Draft', type: 'line', smooth: true, showSymbol: false, areaStyle: { opacity: 0.28 }, data: pack.draft || [] },
                    { name: 'Approved', type: 'line', smooth: true, showSymbol: false, areaStyle: { opacity: 0.22 }, data: pack.approved || [] },
                    { name: 'Active', type: 'line', smooth: true, showSymbol: false, areaStyle: { opacity: 0.22 }, data: pack.active || [] },
                    { name: 'Cancelled', type: 'line', smooth: true, showSymbol: false, areaStyle: { opacity: 0.16 }, data: pack.cancelled || [] },
                ],
            });
        };
        mounted.push({ chart: regional, draw: drawRegional });
        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                quarter = button.getAttribute('data-quarter') || '1';
                buttons.forEach((item) => {
                    const pressed = item === button;
                    item.setAttribute('aria-pressed', pressed ? 'true' : 'false');
                });
                drawRegional();
            });
        });
    }

    const redraw = () => mounted.forEach((item) => item.draw());
    redraw();

    const observer = new MutationObserver(redraw);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    window.addEventListener('resize', () => {
        mounted.forEach((item) => item.chart.resize());
    });
}

export function mountReferenceChart() {
    const source = document.getElementById('reference-chart-data');
    const node = document.getElementById('chart-reference');

    if (!source || !node) {
        return;
    }

    let data;

    try {
        data = JSON.parse(source.textContent || '{}');
    } catch {
        return;
    }

    const chart = init(node);
    const draw = () => {
        const dark = document.documentElement.classList.contains('dark');
        const muted = dark ? '#cbd5e1' : '#64748b';
        const split = dark ? '#334155' : '#e2e8f0';
        chart.setOption({
            backgroundColor: 'transparent',
            color: ['#0f6e56'],
            tooltip: { trigger: 'axis' },
            grid: { left: 40, right: 16, top: 24, bottom: 72 },
            xAxis: {
                type: 'category',
                data: data.labels || [],
                axisLabel: { color: muted, interval: 0, rotate: 28 },
                axisLine: { lineStyle: { color: split } },
            },
            yAxis: {
                type: 'value',
                minInterval: 1,
                axisLabel: { color: muted },
                splitLine: { lineStyle: { color: split } },
            },
            series: [{ name: data.series || 'Records', type: 'bar', data: data.values || [], barMaxWidth: 36 }],
        });
    };

    draw();
    const observer = new MutationObserver(draw);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    window.addEventListener('resize', () => chart.resize());
}

export function mountDashboardCharts() {
    const source = document.getElementById('dashboard-chart-data');

    if (!source) {
        return;
    }

    let charts;

    try {
        charts = JSON.parse(source.textContent || '{}');
    } catch {
        return;
    }

    const mounted = [];
    const theme = () => {
        const dark = document.documentElement.classList.contains('dark');

        return {
            text: dark ? '#e2e8f0' : '#0f172a',
            muted: dark ? '#cbd5e1' : '#64748b',
            split: dark ? '#334155' : '#e2e8f0',
        };
    };

    const pieNode = document.getElementById('chart-dashboard-states');

    if (pieNode && charts.states) {
        const pie = init(pieNode);
        mounted.push({
            chart: pie,
            draw() {
                const colors = theme();
                pie.setOption({
                    backgroundColor: 'transparent',
                    color: palette,
                    tooltip: { trigger: 'item' },
                    series: [{
                        type: 'pie',
                        radius: ['36%', '58%'],
                        label: { show: true, color: colors.text, fontSize: 11, formatter: '{b}\n{c}' },
                        labelLine: { lineStyle: { color: colors.muted } },
                        data: (charts.states.labels || []).map((name, index) => ({
                            name,
                            value: (charts.states.values || [])[index] || 0,
                        })),
                    }],
                });
            },
        });
    }

    const trendNode = document.getElementById('chart-dashboard-trend');

    if (trendNode && charts.trend) {
        const trend = init(trendNode);
        mounted.push({
            chart: trend,
            draw() {
                const colors = theme();
                trend.setOption({
                    backgroundColor: 'transparent',
                    color: ['#0f6e56'],
                    tooltip: { trigger: 'axis' },
                    grid: { left: 36, right: 12, top: 24, bottom: 32 },
                    xAxis: {
                        type: 'category',
                        data: charts.trend.labels || [],
                        axisLabel: { color: colors.muted },
                        axisLine: { lineStyle: { color: colors.split } },
                    },
                    yAxis: {
                        type: 'value',
                        minInterval: 1,
                        axisLabel: { color: colors.muted },
                        splitLine: { lineStyle: { color: colors.split } },
                    },
                    series: [{
                        name: 'Opened',
                        type: 'line',
                        smooth: true,
                        showSymbol: false,
                        areaStyle: { opacity: 0.18 },
                        data: charts.trend.values || [],
                    }],
                });
            },
        });
    }

    const bars = [
        ['chart-dashboard-regions', charts.regions, 'Opportunities'],
        ['chart-dashboard-sectors', charts.sectors, 'Opportunities'],
    ];

    bars.forEach(([elementId, data, seriesName]) => {
        const node = document.getElementById(elementId);

        if (!node || !data) {
            return;
        }

        const chart = init(node);
        mounted.push({
            chart,
            draw() {
                const colors = theme();
                chart.setOption({
                    backgroundColor: 'transparent',
                    color: palette,
                    tooltip: { trigger: 'axis' },
                    grid: { left: 36, right: 12, top: 16, bottom: 72 },
                    xAxis: {
                        type: 'category',
                        data: data.labels || [],
                        axisLabel: { color: colors.muted, interval: 0, rotate: 28 },
                        axisLine: { lineStyle: { color: colors.split } },
                    },
                    yAxis: {
                        type: 'value',
                        minInterval: 1,
                        axisLabel: { color: colors.muted },
                        splitLine: { lineStyle: { color: colors.split } },
                    },
                    series: [{ name: seriesName, type: 'bar', data: data.values || [], barMaxWidth: 28 }],
                });
            },
        });
    });

    const redraw = () => mounted.forEach((item) => item.draw());
    redraw();
    const observer = new MutationObserver(redraw);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    window.addEventListener('resize', () => mounted.forEach((item) => item.chart.resize()));
}
