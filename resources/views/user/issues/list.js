Alpine.data('ListPage', () =>
    window.AlpineComponents.ListPage({
        data: PageData.issues,

        defaultParams: {
            page: 1,
            status: 'all',
            type: 'all',
            priority: 'all',
            assignee: 'all',
            search_value: '',
            sort: '-id',
        },

        open(issue) {
            window.location.href = zroute('user.issues.show', issue.key);
        },
    })
);
