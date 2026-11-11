Alpine.data('ListPage', () =>
    window.AlpineComponents.ListPage({
        data: PageData.issues,

        defaultParams: {
            page: 1,
            status: 'all',
            type: 'all',
            priority: 'all',
            assignee: 'all',
            open: 0,
            released_this_week: 0,
            search_value: '',
            sort: '-id',
            ...(PageData.defaultParams ?? {}),
        },

        filterBy(params) {
            Object.assign(this.params, { status: 'all', assignee: 'all', open: 0, released_this_week: 0, page: 1 }, params);
            this.load();
            document.getElementById('all-issues')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        open(issue) {
            window.location.href = zroute('user.issues.show', issue.key);
        },
    })
);
