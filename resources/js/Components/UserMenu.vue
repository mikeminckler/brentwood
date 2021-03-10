<template>

    <div class="relative md:mr-2" v-if="user">

        <div class="rounded border border-gray-300 bg-white overflow-hidden h-8 w-8 cursor-pointer" @click="showMenu = !showMenu" @mouseleave="hideMenu()">
            <img :src="user.avatar" v-if="user.avatar" class="object-cover w-full h-full" />
            <div class="w-full h-full flex items-center justify-center" v-if="!user.avatar"><i class="fas fa-user"></i></div>
        </div>

        <transition name="fade">
            <div class="absolute shadow right-0 z-3 bg-white mt-2" v-if="showMenu" @mouseleave="hoverOff()" @mouseenter="hover = true">
                <div class="whitespace-nowrap px-2 py-1">{{ user.name }}</div>
                <div class="text-primary px-2 py-1 hover:bg-gray-200 flex cursor-pointer" @click="logout(false)">
                    <div class="pr-1">Logout</div>
                    <div class="icon"><i class="fas fa-sign-out-alt"></i></div>
                </div>
            </div>
        </transition>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback'

    export default {

        props: ['user'],
        mixins: [Feedback],
        data() {
            return {
                showMenu: false,
                hover: false,
                timeoutTimer: null,
                activity: _.throttle( function() {
                    this.setActivity();
                }, 500),
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {
            this.$store.dispatch('setUser', this.user);
            this.timeoutTimer = setInterval(this.timeoutCheck, 30000); // every minute

            const listener = event => {
                this.activity();
            };

            document.addEventListener('mousemove', listener);
            document.addEventListener('keyup', listener);
            document.addEventListener('click', listener);

            this.$once('hook:destroyed', () => {
                document.removeEventListener('mousemove', listener);
                document.removeEventListener('keyup', listener);
                document.removeEventListener('click', listener);
            });
        },

        methods: {
            logout: function(timeout) {
                this.showMenu = false;

                let input = {};
                if (timeout) {
                    input = {timeout: true};
                }
                this.$http.post('/logout', input).then( response => {
                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                });
            },

            hoverOff: function() {
                this.hover = false;
                this.hideMenu();
            },
            
            hideMenu: _.debounce( function() {
                if (!this.hover) {
                    this.showMenu = false;
                    this.hover = false;
                }
            }, 1000),

            timeoutCheck: function() {
                if (this.$store.state.activity) {
                    this.$store.dispatch('setActivity', false);
                    this.$http.post('/session-timeout').then( response => {
                    }, error => {
                    });
                } else {
                    this.$http.get('/session-timeout').then( response => {
                    }, error => {
                        this.logout(true);
                    });
                }
            },

            setActivity: function() {
                if (this.$store.state.activity == false) {
                    this.$store.dispatch('setActivity', true);
                }
            },
        },

    }
</script>
