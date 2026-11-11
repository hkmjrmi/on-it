Alpine.data('IssuePage', () => ({
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
