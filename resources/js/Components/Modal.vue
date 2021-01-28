<template>

    <div class="fixed top-0 left-0 w-screen h-screen z-30">

        <div class="flex items-center justify-center max-h-screen h-full relative">
            <div class="bg-white bg-opacity-75 absolute w-screen h-screen z-20" @click.stop="$emit('close')"></div>
            <div class="relative shadow bg-gray-100 p-2 md:p-4 z-25 max-h-screen">

                <div @click="$emit('close')" 
                    class="text-lg hover:text-gray-800 absolute top-0 right-0 -mt-2 -mr-2 z-5 bg-gray-100 shadow rounded-full p-1 cursor-pointer flex items-center justify-center"
                ><i class="fas fa-times-circle"></i></div>

                <div class="relative max-h-screen overflow-y-scroll">
                    <slot></slot>
                </div>

            </div>

        </div>
    
    </div>

</template>

<script>
    export default {

        props: [],
        data() {
            return {
            
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {

            document.addEventListener('keyup', this.modalKeys);

            this.$eventer.$on('close-modal', () => {
                this.$emit('close');
            });

            this.$once('hook:destroyed', () => {
                this.$eventer.$off('close-modal');
            });

        },

        methods: {

            modalKeys: function(e) {
                let code = e.keyCode;
                if (code == 27) {
                    this.$emit('close');
                }
            },

        },

    }
</script>
