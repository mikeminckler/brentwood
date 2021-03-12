<template>

    <div class="md:-mx-16 relative md:my-16">
        <div class="hidden md:block photo fill z-2 rounded md:shadow-lg">
            <img class="" src="/images/login.jpg" />
        </div>
        <div class="md:flex w-full md:my-8 relative z-4 p-4 md:px-32 md:py-24">

            <div class="relative">
                <div class="flex w-full items-center justify-center h-full md:py-4 relative">
                    <a href="/login/google" class="h-full cursor-pointer flex items-center justify-center flex-col w-full md:p-4 bg-gray-100 md:shadow rounded-l px-8 relative z-3">
                        <h2 class="text-gray-600">Staff &amp; Students</h2>
                        <img class="p-4" srcset="/images/google_signin.png 1x, /images/google_signin@2x.png 2x" />
                    </a>
                </div>
            </div>

            <div class="flex-1 flex items-center justify-start">
                <div class="p-4 md:px-16 md:py-8 md:bg-gray-100 rounded w-full h-full z-4 md:shadow-md flex justify-center">

                    <div class="w-full max-w-sm">
                        <h1>Login</h1>

                        <div class="form mt-4">
                            <div class="input">
                                <input class="outline-none" type="email" autofocus v-model="email" placeholder="Email" />
                            </div>
                            <div class="input">
                                <input class="outline-none" type="password" v-model="password" placeholder="Password" @key.enter="login()" />
                            </div>

                            <div class="flex items-center">
                                <div class="flex-1">
                                    <button @click.stop.prevent="login()">Login</button>
                                </div>
                                <div class="link whitespace-nowrap">Forgot Password</div>
                            </div>
                        </div>

                    </div>


                </div>

            </div>

            <div class="relative">
                <div class="flex w-full items-center justify-start h-full md:py-4 relative">
                    <div class="h-full cursor-pointer flex items-center md:items-start justify-center flex-col w-full p-4 md:bg-gray-100 md:shadow rounded-r px-8 relative z-3">

                        <h2>New Applicants</h2>

                        <a href="/register" class="mt-4"><div class="button">Register Now</div></a>

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
