export default {

    data() {
        return {
            lockedFields: [],
            focusedField: null,
        }
    },

    mounted() {

        this.$echo.join('page.' + this.$store.state.page.id)

            .here( (users) => {
                this.checkForLockedFields();
            })

            .leaving((user) => {
                let index = this.$lodash.findIndex( this.lockedFields, f => {
                    return f.user === user.name;
                });
                if (index >= 0) {
                    this.lockedFields.splice(index, 1);
                }
                this.focusedField = null;
            })

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
            })

            .listenForWhisper('locked-fields-check', (e) => {
                if (e.uuid === this.uuid) {
                    if (this.focusedField) {
                        this.whisperEditing(this.focusedField);
                    }
                }
            });

    },

    methods: {
        whisperEditing: function(field) {

            this.focusedField = field;
            this.$echo.join('page.' + this.$store.state.page.id)
                .whisper('editing', {
                    uuid : this.uuid,
                    field: field,
                    user: this.$store.state.user,
                });

        },

        whisperEditingComplete: function(field) {

            this.focusedField = null;
            this.$echo.join('page.' + this.$store.state.page.id)
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
        },

        checkForLockedFields: function() {

            this.$echo.join('page.' + this.$store.state.page.id)
                .whisper('locked-fields-check', {
                    uuid : this.uuid,
                });

        }

    },

}
