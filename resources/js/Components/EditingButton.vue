<template>

    <div class="button-icon" 
        :class="editingEnabled ? 'active' : ''" 
        @click="toggleEditing"
    ><i class="fas fa-pencil-alt"></i></div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback'

    export default {

        mixins: [Feedback],
        props: ['enabled', 'type'],

        computed: {
            editingEnabled() {
                return this.$store.state.editing;
            },
        },

        mounted() {
            this.$store.dispatch('setEditing', this.enabled);

            if (this.$store.state.editing) {
                this.$echo.private('role.' + this.type + '-editor')
                    .listen('PageDraftCreated', (data) => {
                        this.$eventer.$emit('refresh-page-tree');
                    });

                this.$once('hook:destroyed', () => {
                    this.$echo.leave('role.' + this.type + '-editor');
                });
            }
        },

        methods: {

            toggleEditing: function() {

                this.$http.post('/editing-toggle/' + this.type).then( response => {
                    location.reload();
                }, error => {
                    this.processErrors(error.response);
                });

            }
        },

    }
</script>
