<template>

    <div class="flex relative">

        <div class="flex-1 relative flex flex-col">

            <div class="absolute w-full h-full">
                <photo-controls :photo="photo" 
                    :fill="1" 
                    :content="content"
                    :photos="photos"
                    @remove="removePhoto(photo, 0)"
                ></photo-controls>

                <div class="photo" :class="photo.fill ? 'fill' : 'fit'" v-if="photo">
                    <img :src="photo.large" :style="'object-position: ' + photo.offsetX + '% ' + photo.offsetY + '%;'">
                </div>

                <div class="flex-1 flex flex-col items-center justify-center w-full py-16" v-if="!photo">
                    <div class="button" @click="$eventer.$emit('add-files', fileUploadName)">
                        <div class="">Upload A Photo</div>
                    </div>

                    <file-uploads
                        :name="fileUploadName"
                        v-model="uploads"
                        multiple="false"
                        :items="photos"
                        type="image"
                    ></file-uploads>

                </div>
            </div>

        </div>

        <div class="flex-2">

            <div class="text-block">
                <div class="h2">
                    <input type="text" v-model="content.header" placeholder="Header" />
                </div>

                <editor v-model="content.body" 
                    placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit."
                ></editor>

                <div v-if="first" class="h-1 w-16 bg-gray-400 my-4"></div>
            </div>

        </div>

    </div>

</template>

<script>

    import Editor from '@/Components/Editor.vue';
    import FileUploads from '@/Components/FileUploads';
    import Photos from '@/Mixins/Photos';
    import Feedback from '@/Mixins/Feedback';
    import PhotoControls from '@/Components/PhotoControls';

    export default {

        props: [ 'content', 'uuid', 'first'],
        mixins: [ Photos, Feedback ],

        components: {
            'editor': Editor,
            'file-uploads': FileUploads,
            'photo-controls': PhotoControls,
        },

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
        },

    }
</script>
