<template>

    <div class="px-2 py-1 cursor-pointer shadow" :class="editingEnabled ? 'bg-primary text-white' : 'hover:bg-white hover:shadow-md'" @click="toggleEditing"><i class="fas fa-marker"></i></div>

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
