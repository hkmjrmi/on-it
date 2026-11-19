Alpine.data('ListPage', () =>
    window.AlpineComponents.ListPage({
        data: PageData.issues,

        defaultParams: {
            page: 1,
            status: 'all',
            type: 'all',
            system: 'all',
            priority: 'all',
            assignee: 'all',
            open: 0,
            released_this_week: 0,
            archived: 0,
            search_value: '',
            sort: '-id',
            ...(PageData.defaultParams ?? {}),
        },

        filterBy(params) {
            Object.assign(this.params, { status: 'all', assignee: 'all', open: 0, released_this_week: 0, archived: 0, page: 1 }, params);
            this.load();
        },

        savedFilters: PageData.savedFilters ?? [],

        selected: [],

        bulk: { action: 'status', status: 'in_development', priority: 'medium', assigned_to: '' },

        applyBulk() {
            const field = { status: 'status', priority: 'priority', assignee: 'assigned_to' }[this.bulk.action];
            const payload = { ids: this.selected, action: this.bulk.action };

            if (field) {
                payload[field] = this.bulk[field] === '' ? null : this.bulk[field];
            }

            Helper.showPageSpinner();

            axios
                .post(zroute('user.issues.bulk'), payload)
                .then((response) => {
                    const { message, skipped } = response.data;

                    if (skipped.length > 0) {
                        const details = skipped.map((item) => eh(item.key) + ': ' + eh(item.reason)).join('<br>');
                        Helper.showNotificationWarning(eh(message) + '<br>' + __('Skipped') + ':<br>' + details, 8000);
                    } else {
                        Helper.showNotificationSuccess(message);
                    }

                    this.selected = [];
                    this.load(this.url, Object.assign({}, this.params, { _ajax: 1 }));
                })
                .catch((error) => {
                    const errors = error.response?.data?.errors;
                    Helper.showNotificationError(errors ? Object.values(errors)[0][0] : error.response?.data?.message);
                })
                .then(() => Helper.removePageSpinner());
        },

        applyFilter(filter) {
            const { tab, ...defaults } = this.defaultParams;
            Object.assign(this.params, defaults, filter.params, { page: 1 });
            this.load();
        },

        currentFilterParams() {
            const { page, tab, ...params } = Alpine.raw(this.params);

            return params;
        },

        saveFilter() {
            this.filterModal(__('Save Filter'), '', (name) => axios.post(zroute('user.saved_filters.store'), { name, params: this.currentFilterParams() }));
        },

        renameFilter(filter) {
            this.filterModal(__('Rename Filter'), filter.name, (name) => axios.patch(zroute('user.saved_filters.update', filter.id), { name }));
        },

        removeFilter(filter) {
            abmodal({
                message: __('Do you want to delete this saved filter?') + '<br>' + eh(filter.name),
                onEscape: true,
                buttons: {
                    yes: {
                        label: __('Yes'),
                        className: 'btn-danger',
                        callback: () => {
                            axios
                                .delete(zroute('user.saved_filters.destroy', filter.id))
                                .then((response) => {
                                    this.savedFilters = response.data.filters;
                                    Helper.showNotificationSuccess(response.data.message);
                                })
                                .catch((error) => Helper.showNotificationError(error.response?.data?.message));
                        },
                    },
                    no: {
                        label: __('No'),
                    },
                },
            });
        },

        filterModal(title, name, request) {
            const page = this;

            abmodal({
                isForm: true,
                title,
                message: document.getElementById('saved_filter_modal'),
                backdrop: true,
                closeButton: true,
                onEscape: true,
                buttons: {
                    submit: {
                        label: __('Save'),
                        className: 'btn-primary',
                        type: 'submit',
                        callback: false,
                    },
                    cancel: {
                        label: __('Cancel'),
                    },
                },
                data: window.AlpineComponents.Form({
                    formRequest: PageData.savedFilterRequest,

                    data: { name },

                    submit() {
                        request(this.data.name.trim())
                            .then((response) => {
                                page.savedFilters = response.data.filters;
                                this.close();
                                Helper.showNotificationSuccess(response.data.message);
                            })
                            .catch((error) => {
                                const errors = error.response?.data?.errors;

                                if (errors?.name) {
                                    this.setErrors({ name: errors.name });
                                    this.showField('name');
                                } else {
                                    Helper.showNotificationError(error.response?.data?.message);
                                }
                            });
                    },
                }),
            });
        },

        open(issue) {
            window.location.href = zroute('user.issues.show', issue.key);
        },
    })
);
