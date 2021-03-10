<template>

    <div class="w-full flex flex-col items-center">

        <div class="flex w-full my-4">

            <div class="flex-1">
                <div class="flex w-full items-center justify-center h-full">
                    <div class="cursor-pointer flex items-center flex-col w-full m-4 p-4 bg-gray-200 border rounded hover:border-gray-300" @click="redirectToGoogle()">
                        <div class="">Staff &amp; Students</div>
                        <div class="p-2"><img class="h-12" src="/images/logo.svg" /></div>
                        <img src="/images/google_signin.png" />
                    </div>
                </div>
            </div>
            <div class="flex-2 flex items-center justify-center">
                <div class="px-8 py-4 max-w-sm w-full bg-gray-200 rounded border">
                    <h1>Login</h1>

                    <div class="form mt-2">
                        <div class="input">
                            <input class="outline-none" type="email" autofocus v-model="email" placeholder="Email" />
                        </div>
                        <div class="input">
                            <input class="outline-none" type="password" v-model="password" placeholder="Password" @key.enter="login()" />
                        </div>

                        <button @click.stop.prevent="login()">Login</button>

                    </div>
                </div>
                <div class="py-4 h-full">
                    <div class="bg-white rounded-r p-4 h-full border w-48">
                        <h3>Register Now</h3>
                        <p>Please complete our account sign up form to start your Brentwood experience</p>
                    </div>
                </div>
            </div>

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
                email: '',
                password: '',
                type: null,
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {

            let params = new URLSearchParams(window.location.search.slice(1));
            if (params.get('timeout')) {
                this.$store.dispatch('addFeedback', {'type': 'error', 'message': 'Session Expired'});
            }

            if (params.get('logout')) {
                this.$store.dispatch('addFeedback', {'type': 'success', 'message': 'Logout Complete'});
            }
        },

        methods: {
            login: function() {

                this.$http.post('/login', {email: this.email, password: this.password}).then( response => {
                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                });
            },

            redirectToGoogle: function() {
                window.location.href = '/login/google';
            }
        },

    }
</script>
