<template>

    <div class="relative z-2 w-full my-4 bg-gray-200 overflow-hidden shadow">

        <div class="relative flex items-center justify-center w-full flex-wrap">
            <div class="relative z-2 button mx-2 my-1" @click="add('addTextBlock')">
                <div class="pr-2"><i class="fas fa-align-justify"></i></div>
                <div class="whitespace-no-wrap">Text</div>
            </div>

            <div class="relative z-2 button mx-2 my-1" @click="add('addPhotoBlock')">
                <div class="pr-2"><i class="fas fa-file-image"></i></div>
                <div class="whitespace-no-wrap">Photos</div>
            </div>

            <div class="relative z-2 button mx-2 my-1 items-center" @click="add('addYoutubeVideo')">
                <div class="pr-2 text-lg leading-none"><i class="fab fa-youtube"></i></div>
                <div class="whitespace-no-wrap">YouTube Video</div>
            </div>

            <div class="relative z-2 button mx-2 my-1" @click="add('addQuote')">
                <div class="pr-2"><i class="fas fa-quote-left"></i></div>
                <div class="whitespace-no-wrap">Testimonial</div>
            </div>

            <div class="relative z-2 button mx-2 my-1" @click="add('addBannerPhoto')">
                <div class="pr-2"><i class="fas fa-align-justify"></i></div>
                <div class="whitespace-no-wrap">Banner Photo</div>
            </div>

            <div class="relative z-2 button mx-2 my-1 items-center" @click="add('addEmbedCode')">
                <div class="pr-2 text-lg leading-none"><i class="fas fa-code"></i></div>
                <div class="whitespace-no-wrap">HTML</div>
            </div>

            <div class="relative z-2 button mx-2 my-1 items-center" @click="add('addBlogList')">
                <div class="pr-2 text-lg leading-none"><i class="fas fa-blog"></i></div>
                <div class="whitespace-no-wrap">Blog Listing</div>
            </div>

            <div class="relative z-2 button mx-2 my-1 items-center" @click="add('addInstance')">
                <div class="pr-2 text-lg leading-none"><i class="fas fa-copy"></i></div>
                <div class="whitespace-no-wrap">Instance</div>
            </div>
        </div>

    </div>

</template>

<script>
    export default {

        props: ['sortOrder'],
        data() {
            return {
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {
        },

        methods: {

            add: function(type) {
                this.$emit('selected');
                this.$eventer.$emit('add-content-element', {type: type, sortOrder: this.sortOrder})
            }
        },

    }
</script>
