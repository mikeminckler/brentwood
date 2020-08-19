export default {

    methods: {
        whisperEditing: function() {

            this.$echo.private('page.' + this.$store.state.page.id)
                .whisper('editing', {
                    contentElement: this.contentElement,
                });

        }
    },

}
