Alpine.data('IssuePage', () => ({
    editComment(comment) {
        abmodal({
            isForm: true,
            title: __('Edit Comment'),
            message: document.getElementById('comment_edit_modal'),
            backdrop: true,
            closeButton: true,
            onEscape: true,
            buttons: {
                submit: {
                    label: __('Update'),
                    className: 'btn-primary',
                    type: 'submit',
                    callback: false,
                },
                cancel: {
                    label: __('Cancel'),
                },
            },
            data: window.AlpineComponents.Form({
                formRequest: PageData.commentFormRequest,

                data: {
                    body: comment.body,
                },

                init() {
                    this.action = zroute('user.issues.comments.update', [PageData.issue.key, comment.id]);
                },

                submit() {
                    Helper.showPageSpinner();

                    axios
                        .patch(this.action, this.data)
                        .then((response) => {
                            Bolt.once('unload', () => {
                                this.close();
                                Helper.removePageSpinner();
                                Helper.showNotificationSuccess(response.data.message);
                            });

                            Bolt.preserveScroll().go(window.location.href);
                        })
                        .catch((error) => {
                            Helper.removePageSpinner();

                            const errors = error.response?.data?.errors;

                            if (errors) {
                                this.setErrors(errors);
                                this.showField(Object.keys(errors)[0]);
                            } else {
                                Helper.showNotificationError(error.response?.data?.message);
                            }
                        });
                },
            }),
        });
    },

    removeComment(commentId) {
        abmodal({
            message: __('Do you want to delete this comment?') + '<br><small class="text-muted">' + __('This cannot be undone.') + '</small>',
            onEscape: true,
            buttons: {
                yes: {
                    label: __('Yes'),
                    className: 'btn-danger',
                    callback: () => {
                        Helper.showPageSpinner();

                        axios
                            .delete(zroute('user.issues.comments.destroy', [PageData.issue.key, commentId]))
                            .then((response) => {
                                Bolt.once('unload', () => {
                                    Helper.removePageSpinner();
                                    Helper.showNotificationSuccess(response.data.message);
                                });

                                Bolt.preserveScroll().go(window.location.href);
                            })
                            .catch((error) => {
                                Helper.removePageSpinner();
                                Helper.showNotificationError(error.response?.data?.message);
                            });
                    },
                },
                no: {
                    label: __('No'),
                },
            },
        });
    },

    remove() {
        abmodal({
            message: __('Do you want to delete this issue?') + '<br>' + eh(PageData.issue.key + ' ' + PageData.issue.title) + '<br><small class="text-muted">' + __('This cannot be undone.') + '</small>',
            onEscape: true,
            buttons: {
                yes: {
                    label: __('Yes'),
                    className: 'btn-danger',
                    callback: () => {
                        Helper.showPageSpinner();

                        axios
                            .delete(zroute('user.issues.destroy', PageData.issue.key))
                            .then((response) => {
                                Bolt.once('unload', () => {
                                    Helper.removePageSpinner();
                                    Helper.showNotificationSuccess(response.data.message);
                                });

                                Bolt.go(response.data.redirect);
                            })
                            .catch((error) => {
                                Helper.removePageSpinner();
                                Helper.showNotificationError(error.response?.data?.message);
                            });
                    },
                },
                no: {
                    label: __('No'),
                },
            },
        });
    },

    edit() {
        abmodal({
            isForm: true,
            title: __('Edit Issue'),
            message: document.getElementById('issue_edit_modal'),
            backdrop: true,
            closeButton: true,
            onEscape: true,
            size: 'large',
            buttons: {
                submit: {
                    label: __('Update'),
                    className: 'btn-primary',
                    type: 'submit',
                    callback: false,
                },
                cancel: {
                    label: __('Cancel'),
                },
            },
            data: window.AlpineComponents.Form({
                formRequest: PageData.editFormRequest,

                data: {
                    title: PageData.issue.title,
                    description: PageData.issue.description,
                },

                init() {
                    this.action = zroute('user.issues.update', PageData.issue.key);
                },

                submit() {
                    Helper.showPageSpinner();

                    axios
                        .patch(this.action, this.data)
                        .then((response) => {
                            Bolt.once('unload', () => {
                                this.close();
                                Helper.removePageSpinner();
                                Helper.showNotificationSuccess(response.data.message);
                            });

                            Bolt.go(window.location.href);
                        })
                        .catch((error) => {
                            Helper.removePageSpinner();

                            const errors = error.response?.data?.errors;

                            if (errors) {
                                this.setErrors(errors);
                                this.showField(Object.keys(errors)[0]);
                            } else {
                                Helper.showNotificationError(error.response?.data?.message);
                            }
                        });
                },
            }),
        });
    },
}));
