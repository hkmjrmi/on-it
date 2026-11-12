(() => {
    const color = (name) => Helper.cssVar(name);

    const countScales = {
        y: { beginAtZero: true, grace: '10%', ticks: { precision: 0 } },
    };

    const bar = (labels, values, title, colors) => ({
        type: 'bar',
        data: {
            labels,
            datasets: [{ label: __('Issues'), data: values, backgroundColor: colors ?? color('--bs-primary') }],
        },
        options: {
            maintainAspectRatio: false,
            scales: countScales,
            plugins: { title: { display: true, text: title }, legend: { display: false } },
        },
    });

    const configs = {
        createdResolved: (report) => ({
            type: 'line',
            data: {
                labels: report.weeks,
                datasets: [
                    { label: __('Created'), data: report.created, borderColor: color('--bs-primary'), backgroundColor: color('--bs-primary'), borderWidth: 2 },
                    { label: __('Resolved'), data: report.resolved, borderColor: color('--bs-success'), backgroundColor: color('--bs-success'), borderWidth: 2 },
                ],
            },
            options: {
                maintainAspectRatio: false,
                scales: countScales,
                plugins: { title: { display: true, text: __('Created vs resolved per week') } },
            },
        }),

        daysToRelease: (report) => ({
            type: 'line',
            data: {
                labels: report.weeks,
                datasets: [
                    {
                        label: __('Avg. days to release'),
                        data: report.days_to_release,
                        borderColor: color('--bs-info'),
                        backgroundColor: color('--bs-info'),
                        borderWidth: 2,
                        spanGaps: true,
                    },
                ],
            },
            options: {
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, grace: '10%' } },
                plugins: { title: { display: true, text: __('Average days from created to released') }, legend: { display: false } },
            },
        }),

        bySystem: (report) => bar(report.by_system.labels, report.by_system.values, __('Created by system')),

        byType: (report) => bar(report.by_type.labels, report.by_type.values, __('Created by type')),

        openByPriority: (report) => {
            const priorityColors = { low: '--bs-secondary', medium: '--bs-info', high: '--bs-warning', urgent: '--bs-danger' };

            return bar(
                report.open_by_priority.labels,
                report.open_by_priority.values,
                __('Open issues by priority'),
                report.open_by_priority.keys.map((key) => color(priorityColors[key] ?? '--bs-primary'))
            );
        },
    };

    Alpine.data('ReportChart', (type) => ({
        chart: null,

        init() {
            this.chart = new Chart(this.$refs.canvas.getContext('2d'), configs[type](PageData.report));
            this.loaded = true;
        },

        destroy() {
            this.chart?.destroy();
        },
    }));
})();
