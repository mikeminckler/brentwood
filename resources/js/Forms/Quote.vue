<template>

    <div class="relative z-2">

        <div class="flex justify-center">

            <div class="relative flex-1 bg-gray-200" style="max-width: 50%;">

                <div class="p-4 absolute z-2 font-light text-gray-400 leading-none" style="font-size: 150px">&ldquo;</div>
                
                <div class="px-16 py-8 text-gray-800">
                    <editor v-model="content.body" 
                            placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit."
                            class="mt-4 relative z-3 italic leading-relaxed"
                    ></editor>

                    <div class="h-1 w-16 bg-primary mb-4"></div>

                    <div class="flex flex-col w-full items-end">
                        <div class="inline-form">
                            <input type="text" v-model="content.author_name" placeholder="Author" />
                        </div>
                        <div class="inline-form mt-1">
                            <input type="text" v-model="content.author_details" placeholder="Author Title / Position" />
                        </div>
                    </div>
                </div>

            </div>

            <div class="flex-1 flex bg-gray-100 relative flex-col">

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
                        multiple="false"
                        :items="photos"
                        type="image"
                    ></file-uploads>
                </div>

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

        props: [ 'content', 'uuid' ],

        mixins: [Feedback, Photos],

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
