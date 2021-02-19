export default {

    data() {
        return {
            saving: false,
            setSaving: _.debounce( function() {
                this.saving = this.isSaving;
            }, 500),
        }
    },

    computed: {
        isSaving() {
            return this.$store.state.saving.length ? true : false;
        }
    },

    watch: {
        isSaving() {
            if (this.isSaving) {
                this.saving = true;
            } else {
                this.setSaving();
            }
        }
    },
}
