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
                    <img :src="photo.large" :style="'object-position: ' + photo.offsetX + '% ' + photo.offsetY + '%;'">
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

        <div class="relative flex -mt-32 justify-center">

            <div class="bg-white px-16 py-8 text-gray-600 w-full max-w-2xl shadow-lg z-4">

                <div class="">
                    <input :class="first ? 'h1' : 'h2'" type="text" v-model="content.header" placeholder="Header" />
                </div>

                <editor v-model="content.body" 
                        placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit."
                        class="mt-4 relative leading-relaxed"
                ></editor>

                <div class="h-1 w-16 bg-gray-400 mt-4"></div>

            </div>

        </div>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';
    import Editor from '@/Components/Editor.vue';
    import FileUploads from '@/Components/FileUploads';
    import Photos from '@/Mixins/Photos';
    import PhotoControls from '@/Components/PhotoControls';

    export default {

        props: [ 'content', 'uuid', 'first' ],

        mixins: [Feedback, Photos],

        components: {
            'editor': Editor,
            'file-uploads': FileUploads,
            'photo-controls': PhotoControls,
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
