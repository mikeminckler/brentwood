<template>

    <div class="md:flex">

        <div class="flex-1"></div>

        <div class="flex-2 flex justify-center">

            <div class="text-block my-8" v-if="!resetComplete">
                <h1>Reset</h1>

                <p>Hello {{ user.name }}. Please reset your password below. Your new password <strong>must be at least 6 characters</strong>.</p>

                <div class="form mt-4">

                    <div class="form-label">Email</div>
                    <div class="fake-input">{{ user.email }}</div>

                    <div class="input">
                        <form-label label="Create Password" :value="password"></form-label>
                        <div class=""><input type="password" id="password" v-model="password" class="outline-none focus:border-gray-400" placeholder="Create Password" /></div>
                        <form-error :errors="formErrors" name="password"></form-error>
                    </div>

                    <div class="input">
                        <form-label label="Confirm Password" :value="password_confirmation"></form-label>
                        <div class=""><input type="password" id="password_confirmation" v-model="password_confirmation" class="outline-none focus:border-gray-400" placeholder="Confirm Password" /></div>
                        <form-error :errors="formErrors" name="password_confirmation"></form-error>
                    </div>

                    <div class="flex-1">
                        <button @click.stop.prevent="resetPassword()">Reset Password</button>
                    </div>
                </div>
            </div>

            <div class="text-block my-8" v-if="resetComplete">

                <h1>Reset Complete</h1>
                
                <p>Your password has been reset and you are now logged in.</p>

                <p>If you need any further assistance please email <a href="mailto:helpdesk@brentwood.bc.ca">helpdesk@brentwood.bc.ca</a></p>

                <p><a class="button" href="/">Proceed to Home Page</a></p>

            </div>

        </div>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';

    export default {

        props: ['user'],

        mixins: [Feedback],

        components: {
            'form-label': () => import(/* webpackChunkName: "form-label" */ '@/Components/FormLabel.vue'),
            'form-error': () => import(/* webpackChunkName: "form-error" */ '@/Components/FormError.vue'),
        },

        data() {
            return {
                password: '',
                password_confirmation: '',
                resetComplete: false,
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {
        },

        methods: {
            resetPassword: function() {

                this.$http.post(window.location.href, {password: this.password, password_confirmation: this.password_confirmation}).then( response => {
                    this.processSuccess(response);
                    this.resetComplete = true;
                }, error => {
                    this.processErrors(error.response);
                });
            }
        },

    }
</script>
