<template>

    <div class="relative z-2 flex items-center w-full justify-center mt-4">
        <div class="cursor-pointer hover:bg-white relative text-primary text-2xl leading-none z-2 p-2" @click="show = !show">
            <i class="fas fa-plus-square"></i>
        </div>

        <transition name="add-content">
            <div class="flex overflow-hidden" v-if="show">
                <div class="relative flex items-center">
                    <div class="absolute bg-primary h-1 w-full z-1" style="top: 45%"></div>
                    <div class="relative z-2 button mx-2" @click="add('addTextBlock')">
                        <div class="pr-2"><i class="fas fa-align-justify"></i></div>
                        <div class="whitespace-no-wrap">Text</div>
                    </div>

                    <div class="relative z-2 button mx-2" @click="add('addPhotoBlock')">
                        <div class="pr-2"><i class="fas fa-file-image"></i></div>
                        <div class="whitespace-no-wrap">Photos</div>
                    </div>

                    <div class="relative z-2 button mx-2 items-center" @click="add('addYoutubeVideo')">
                        <div class="pr-2 text-lg leading-none"><i class="fab fa-youtube"></i></div>
                        <div class="whitespace-no-wrap">YouTube Video</div>
                    </div>

                    <div class="relative z-2 button mx-2" @click="add('addQuote')">
                        <div class="pr-2"><i class="fas fa-quote-left"></i></div>
                        <div class="whitespace-no-wrap">Testimonial</div>
                    </div>

                    <div class="relative z-2 button mx-2" @click="add('addBannerPhoto')">
                        <div class="pr-2"><i class="fas fa-align-justify"></i></div>
                        <div class="whitespace-no-wrap">Banner Photo</div>
                    </div>

                    <div class="relative z-2 button mx-2 items-center" @click="add('addEmbedCode')">
                        <div class="pr-2 text-lg leading-none"><i class="fas fa-code"></i></div>
                        <div class="whitespace-no-wrap">HTML</div>
                    </div>
                </div>
            </div>
        </transition>

    </div>

</template>

<script>
    export default {

        props: ['sortOrder', 'expanded'],
        data() {
            return {
                show: false,
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {
            if (this.expanded) {
                this.show = true;
            }
        },

        methods: {

            add: function(type) {
                this.show = false;
                this.$eventer.$emit('add-content-element', {type: type, sortOrder: this.sortOrder + 1})
            }
        },

    }
</script>

<style>

@keyframes add-content {
    0% {
        max-width: 0;
        opacity: 0;
    }
    100%   {
        max-width: 100vw;
        opacity: 1;
    }
}

.add-content-enter-active {
    animation: add-content calc(var(--transition-time) * 2) ease-out;
}

.add-content-leave-active {
    animation: add-content calc(var(--transition-time) * 2) reverse;
}
</style>
