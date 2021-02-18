<template>

    <div class="relative md:mr-2" v-if="user">

        <div class="rounded-full border-2 border-white bg-white overflow-hidden h-8 w-8 shadow hover:shadow-md cursor-pointer" @click="showMenu = !showMenu">
            <img :src="user.avatar" v-if="user.avatar" class="object-cover w-full h-full" />
            <div class="w-full h-full flex items-center justify-center" v-if="!user.avatar"><i class="fas fa-user"></i></div>
        </div>

        <transition name="fade">
            <div class="absolute shadow right-0 z-3 bg-white mt-2" v-if="showMenu">
                <div class="whitespace-nowrap px-2 py-1">{{ user.name }}</div>
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
                this.showMenu = false;
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
