<template>

    <div class="md:-mx-16 relative md:mb-16">
        <div class="hidden md:block photo fill z-2 rounded md:shadow-lg">
            <img class="" src="/images/login.jpg" />
        </div>

        <div class="md:flex w-full relative z-4 p-4 md:px-32 md:pt-48 md:-mb-16 transition-opacity duration-500" :class="showLogin ? 'opacity-1' : 'opacity-0'">

            <div class="relative duration-500 delay-200 transition-transform transform" :class="showLogin ? 'translate-x-0' : 'translate-x-full'">
                <div class="flex w-full items-center justify-center h-full md:py-4 relative">
                    <a href="/login/google" class="h-full cursor-pointer flex items-center justify-center flex-col w-full md:p-4 md:bg-gray-200 md:shadow rounded-l px-8 relative z-3">
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
                                <form-label label="Email" :value="email"></form-label>
                                <div><input class="outline-none" type="email" autofocus v-model="email" placeholder="Email" @blur="checkForOAuthEmail()" /></div>
                                <form-error :errors="formErrors" name="email"></form-error>
                            </div>
                            <div class="input">
                                <form-label label="Password" :value="password"></form-label>
                                <input class="outline-none" type="password" v-model="password" placeholder="Password" @key.enter="login()" />
                                <form-error :errors="formErrors" name="password"></form-error>
                            </div>

                            <div class="text-gray-500"><checkbox-input v-model="remember" label="Remember Me"></checkbox-input></div>

                            <div class="flex items-center mt-4">
                                <div class="flex-1">
                                    <button @click.stop.prevent="login()">Login</button>
                                </div>
                                <div class="link whitespace-nowrap" @click="showForgotPassword = true">Forgot Password</div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <div class="relative duration-500 delay-200 transition-transform transform" :class="showLogin ? 'translate-x-0' : '-translate-x-full'">
                <div class="flex w-full items-center justify-start h-full md:py-4 relative">
                    <div class="h-full cursor-pointer flex items-center md:items-start justify-center flex-col w-full p-4 md:bg-gray-200 md:shadow rounded-r px-8 relative z-3">

                        <h2>New Applicants</h2>

                        <a href="/register" class="mt-4"><div class="button">Register Now</div></a>

                    </div>
                </div>
            </div>

        </div>

        <transition name="fade">
            <modal v-if="showForgotPassword" @close="showForgotPassword = false">

                <div class="p-4">

                    <h2>Password Reset</h2>

                    <div class="form">

                        <p class="max-w-sm">Please provide your email address and we will send you an email with password reset instructions.</p>

                        <div class="input">
                            <form-label label="Email" :value="email"></form-label>
                            <div><input class="outline-none" type="email" autofocus v-model="email" placeholder="Email" /></div>
                            <form-error :errors="formErrors" name="email" :show="true"></form-error>
                        </div>

                    </div>

                    <button @click.prevent.stop="requestPasswordReset()">Send Password Reset Email</button>
                </div>
            </modal>
        </transition>

    </div>


</template>

<script>

    import Feedback from '@/Mixins/Feedback';

    export default {

        props: [],
        mixins: [Feedback],

        components: {
            'checkbox-input': () => import(/* webpackChunkName: "checkbox-input" */ '@/Components/CheckboxInput.vue'),
            'form-error': () => import(/* webpackChunkName: "form-error" */ '@/Components/FormError.vue'),
            'form-label': () => import(/* webpackChunkName: "form-label" */ '@/Components/FormLabel.vue'),
            'modal': () => import(/* webpackChunkName: "modal" */ '@/Components/Modal.vue'),
        },

        data() {
            return {
                email: '',
                password: '',
                remember: false,
                showForgotPassword: false,
                showLogin: false,
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

            setTimeout( () => {
                this.showLogin = true;
            }, 100);
        },

        methods: {
            login: function() {

                if (this.$lodash.includes(this.email, '@brentwood.ca')) {
                    window.location.href = '/login/google';
                } else {

                    let input = {
                        email: this.email, 
                        password: this.password,
                        remember: this.remember,
                    };

                    this.$http.post('/login', input).then( response => {
                        this.processSuccess(response);
                    }, error => {
                        this.processErrors(error.response);
                    });
                }
            },

            requestPasswordReset: function() {

                this.$http.post('users/request-password-reset', {email: this.email}).then( response => {
                    this.showForgotPassword = false;
                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                });
            },

            checkForOAuthEmail: function() {
                if (this.$lodash.includes(this.email, '@brentwood.ca')) {
                    window.location.href = '/login/google';
                }
            }

        },

    }
</script>
