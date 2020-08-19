export default {

    data() {
        return {
            lockedFields: [],
        }
    },

    mounted() {

        this.$echo.private('page.' + this.$store.state.page.id)
            .listenForWhisper('editing', (e) => {
                if (e.uuid === this.uuid) {
                    let index = this.$lodash.findIndex(this.lockedFields, f => {
                        return f.field === e.field;
                    });
                    if (index < 0) {
                        this.lockedFields.push({
                            field: e.field,
                            user: e.user.name,
                        });
                    }
                }
            })
        
            .listenForWhisper('editing-complete', (e) => {
                if (e.uuid === this.uuid) {
                    let index = this.$lodash.findIndex(this.lockedFields, f => {
                        return f.field === e.field;
                    });
                    if (index >= 0) {
                        this.lockedFields.splice(index, 1);
                    }
                }
            });
    },

    methods: {
        whisperEditing: function(field) {

            this.$echo.private('page.' + this.$store.state.page.id)
                .whisper('editing', {
                    uuid : this.uuid,
                    field: field,
                    user: this.$store.state.user,
                });

        },

        whisperEditingComplete: function(field) {

            this.$echo.private('page.' + this.$store.state.page.id)
                .whisper('editing-complete', {
                    uuid : this.uuid,
                    field: field,
                });

        },

        isLocked: function(field) {
            let f = this.$lodash.find(this.lockedFields, f => {
                return f.field === field;
            });
            return f ? f.user : false;
        }

    },

}
