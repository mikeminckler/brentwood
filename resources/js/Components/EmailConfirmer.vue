<template>

    <div class="fixed right-0 bottom-0 mb-8 mr-8 z-5" v-if="!user.email_verified_at">
        <div class="bg-white shadow border rounded px-4 py-2">

            <h3>Email Confirmation Required</h3>
            <p>Please check your email for a confirmation link.</p>

            <div class="button" @click="resendConfirmationEmail()">Resend Confirmation Email</div>

        </div>
    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';

    export default {

        props: [],

        mixins: [Feedback],

        data() {
            return {
            
            }
        },

        computed: {
            user() {
                return this.$store.state.user;
            }
        },

        watch: {
        },

        mounted() {
        },

        methods: {
            resendConfirmationEmail: function() {

                this.$http.post('/users/' + this.user.id + '/send-email-verification').then( response => {
                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                });

            },
        },

    }
</script>
