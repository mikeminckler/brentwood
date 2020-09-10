<template>

    <div class="relative z-2 w-full mt-4" :class="[show ? 'bg-white' : '']">

        <div class="flex items-center cursor-pointer rounded relative hover:text-primary z-2 p-2" :class="show ? 'text-primary' : ''" @click="show = !show">
            <div class="border-b-2 border-dashed flex-1 transition-colours duration-500" :class="show ? 'border-primary' : 'border-gray-500'"></div>

            <div class="text-2xl leading-none px-2 transition-colours duration-500" :class="show ? 'text-primary' : 'text-gray-600'" title="Add Content">
                <i class="fas fa-plus-square"></i>
            </div>

            <div class="border-b-2 border-dashed flex-1 transition-colours duration-500" :class="show ? 'border-primary' : 'border-gray-500'"></div>
        </div>

        <transition name="add-content">
            <div class="relative flex items-center justify-center w-full flex-wrap pb-2 overflow-hidden" v-if="show">
                <div class="relative z-2 button mx-2 mb-2" @click="add('addTextBlock')">
                    <div class="pr-2"><i class="fas fa-align-justify"></i></div>
                    <div class="whitespace-no-wrap">Text</div>
                </div>

                <div class="relative z-2 button mx-2 mb-2" @click="add('addPhotoBlock')">
                    <div class="pr-2"><i class="fas fa-file-image"></i></div>
                    <div class="whitespace-no-wrap">Photos</div>
                </div>

                <div class="relative z-2 button mx-2 mb-2 items-center" @click="add('addYoutubeVideo')">
                    <div class="pr-2 text-lg leading-none"><i class="fab fa-youtube"></i></div>
                    <div class="whitespace-no-wrap">YouTube Video</div>
                </div>

                <div class="relative z-2 button mx-2 mb-2" @click="add('addQuote')">
                    <div class="pr-2"><i class="fas fa-quote-left"></i></div>
                    <div class="whitespace-no-wrap">Testimonial</div>
                </div>

                <div class="relative z-2 button mx-2 mb-2" @click="add('addBannerPhoto')">
                    <div class="pr-2"><i class="fas fa-align-justify"></i></div>
                    <div class="whitespace-no-wrap">Banner Photo</div>
                </div>

                <div class="relative z-2 button mx-2 mb-2 items-center" @click="add('addEmbedCode')">
                    <div class="pr-2 text-lg leading-none"><i class="fas fa-code"></i></div>
                    <div class="whitespace-no-wrap">HTML</div>
                </div>
            </div>
        </transition>

    </div>

</template>

<script>
    export default {

        props: ['sortOrder', 'alwaysShow'],
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
            if (this.alwaysShow) {
                this.show = true;
            }
        },

        methods: {

            add: function(type) {
                if (!this.alwaysShow) {
                    this.show = false;
                }
                this.$eventer.$emit('add-content-element', {type: type, sortOrder: this.sortOrder})
            }
        },

    }
</script>

<style>

@keyframes add-content {
    0% {
        max-height: 0;
        opacity: 0;
        @apply pb-0;
    }
    100%   {
        max-height: 50px;
        opacity: 1;
        @apply pb-2;
    }
}

.add-content-enter-active {
    animation: add-content calc(var(--transition-time) * 2) ease-out;
}

.add-content-leave-active {
    animation: add-content calc(var(--transition-time) * 2) reverse;
}
</style>
