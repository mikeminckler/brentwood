<template>

    <div class="relative ml-2" v-if="user">

        <div class="button-icon mr-2 bg-gray-200" @click="showMenu = !showMenu"><i class="fas fa-user"></i></div>

        <transition name="fade">
            <div class="absolute shadow right-0 z-3 bg-white mt-4" v-if="showMenu">
                <div class="whitespace-no-wrap px-2 py-1">{{ user.name }}</div>
                <div class="text-primary px-2 py-1 hover:bg-gray-200 flex cursor-pointer" @click="logout">
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
            }
        },

        computed: {

        },

        watch: {
        },

        mounted() {
            this.$store.dispatch('setUser', this.user);
        },

        methods: {
            logout: function() {
                this.$http.post('/logout').then( response => {
                    window.location.href = '/';
                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                });
            }
        },

    }
</script>
