Alpine.data('BoardPage', () => ({
    dragging: null,
    over: null,

    drop(progress) {
        const card = this.dragging;
        this.dragging = null;
        this.over = null;

        if (!card || card.progress === progress) {
            return;
        }

        Helper.showPageSpinner();

        axios
            .patch(zroute('user.issues.board.move', card.key), { progress })
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
