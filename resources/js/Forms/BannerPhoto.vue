<template>

    <div class="relative z-2">

        <div class="relative pb-40p">
            <div class="absolute w-full h-full bg-gray-100 flex flex-col items-center justify-center" style="min-height: 200px;">

                <photo-controls :photo="photo" 
                    :content="content"
                    :photos="photos"
                    @remove="removePhoto(photo, 0)"
                ></photo-controls>

                <div class="photo" :class="photo.fill ? 'fill' : 'fit'" v-if="photo">
                    <img :src="photo.large" :style="'object-position: ' + photo.offsetX + '% ' + photo.offsetY + '%;'" v-if="photo.large" />
                    <div class="flex items-center justify-center bg-gray-200 h-full relative z-3" v-if="!photo.large">
                        <div class="flex bg-gray-100 text-green-500 px-2 py-1">
                            <div class="spin"><i class="fas fa-sync-alt"></i></div>
                            <div class="ml-1">Processing Image</div>
                        </div>
                    </div>
                </div>

                <div class="flex-1 flex flex-col items-center justify-center w-full" v-if="!photo">
                    <div class="button" @click="$eventer.$emit('add-files', fileUploadName)">
                        <div class="">Upload A Photo</div>
                    </div>

                    <file-uploads
                        :name="fileUploadName"
                        v-model="uploads"
                        :multiple="multiplePhotos"
                        :items="photos"
                        type="image"
                    ></file-uploads>
                </div>

            </div>
        </div>

        <div class="relative flex -mt-16 justify-center">

            <div class="bg-white px-16 py-8 text-gray-600 w-full max-w-2xl shadow-lg z-4">

                <div class="">
                    <input :class="first ? 'h1' : 'h2'" type="text" @blur="saveContent()" v-model="content.header" placeholder="Header" />
                </div>

                <editor v-model="content.body" 
                        placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit."
                        class="mt-4 relative leading-relaxed"
                        @blur="saveContent()"
                ></editor>

                <div class="h-1 w-16 bg-gray-400 mt-4"></div>

            </div>

        </div>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';
    import Photos from '@/Mixins/Photos';
    import SaveContent from '@/Mixins/SaveContent';

    export default {

        props: [ 'content', 'uuid', 'first' ],

        mixins: [Feedback, Photos, SaveContent ],

        components: {
            'editor': () => import(/* webpackChunkName: "editor" */ '@/Components/Editor.vue'),
            'file-uploads': () => import(/* webpackChunkName: "file-uploads" */ '@/Components/FileUploads.vue'),
            'photo-controls': () => import(/* webpackChunkName: "photo-controls" */ '@/Components/PhotoControls.vue'),
        },

        data() {
            return {
                multiplePhotos: false,
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {
        },

        methods: {
        },

    }
</script>
