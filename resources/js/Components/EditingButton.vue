<template>

    <div class="w-6 h-6 flex items-center justify-center cursor-pointer shadow text-sm" 
        :class="editingEnabled ? 'bg-primary text-white' : 'bg-white hover:shadow-md text-gray-500'" 
        @click="toggleEditing"
    ><i class="fas fa-marker"></i></div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback'

    export default {

        mixins: [Feedback],
        props: ['enabled'],

        computed: {
            editingEnabled() {
                return this.$store.state.editing;
            },
        },

        mounted() {
            this.$store.dispatch('setEditing', this.enabled);

            if (this.$store.state.editing) {
                this.$echo.private('role.editor')
                    .listen('PageDraftCreated', (data) => {
                        this.$eventer.$emit('refresh-page-tree');
                    });
            }
        },

        methods: {

            toggleEditing: function() {

                this.$http.post('/editing-toggle').then( response => {
                    location.reload();
                }, error => {
                    this.processErrors(error.response);
                });

            }
        },

    }
</script>
