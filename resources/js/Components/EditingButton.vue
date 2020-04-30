<template>

    <div class="">
        <div class="button-icon" :class="editingEnabled ? 'active' : 'disabled'" @click="toggleEditing"><i class="fas fa-marker"></i></div>
    </div>

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
