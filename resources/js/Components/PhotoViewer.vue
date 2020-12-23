<template>

    <transition name="fade">
        <div v-if="src" class="fixed top-0 left-0 w-screen h-screen z-30 p-8 bg-white bg-opacity-75"
            @click="src = null"
        >

            <div class="relative flex items-center justify-center h-full">
                <div class="relative shadow bg-white" @click.stop="">
                    <div class="text-lg hover:text-gray-800 absolute top-0 right-0 -mt-2 -mr-2 z-5 bg-gray-100 shadow rounded-full p-1 cursor-pointer flex items-center justify-center" 
                        @click="src = null"
                    ><i class="fas fa-times-circle"></i></div>
                    <img :src="src" />
                </div>

            </div>
        
        </div>
    </transition>

</template>

<script>
    export default {

        props: [],
        data() {
            return {
                src: null,
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {

            const setPhoto = photo => {
                this.src = photo;
            };
            this.$eventer.$on('view-photo', setPhoto);

            this.$once('hook:destroyed', () => {
                this.$eventer.$off('view-photo', setPhoto);
            });
        },

        methods: {
        },

    }
</script>
