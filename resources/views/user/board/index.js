Alpine.data('BoardPage', () => ({
    dragging: null,
    over: null,

    drop(status) {
        const card = this.dragging;
        this.dragging = null;
        this.over = null;

        if (!card || card.status === status) {
            return;
        }

        Helper.showPageSpinner();

        axios
            .patch(zroute('user.issues.board.move', card.key), { status })
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
}));
