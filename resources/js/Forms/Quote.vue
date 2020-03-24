<template>

    <div class="relative z-2">


        <div class="flex justify-center">

            <div class="relative flex-1 bg-gray-200" style="max-width: 50%;">

                <div class="p-4 absolute z-2 font-light text-gray-400 leading-none" style="font-size: 150px">&ldquo;</div>
                
                <div class="px-16 py-8">
                    <editor v-model="content.body" 
                            @input="saveContent()"
                            placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit."
                            class="mt-4 relative z-3 italic leading-relaxed"
                    ></editor>

                    <div class="h-1 w-16 bg-primary mb-4"></div>

                    <div class="flex flex-col w-full items-end">
                        <div class="inline-form">
                            <input type="text" v-model="content.author_name" @change="saveContent()" placeholder="Author" />
                        </div>
                        <div class="inline-form mt-1">
                            <input type="text" v-model="content.author_details" @change="saveContent()" placeholder="Author Title / Position" />
                        </div>
                    </div>
                </div>

            </div>

            <div class="flex-1 flex bg-gray-100 relative">

                <div class="absolute right-0 bottom-0 z-3" v-if="photo">
                    <div class="mx-1 remove-icon" @click="removePhoto(photo, 0)"><i class="fas fa-times"></i></div>
                </div>

                <div class="photo" v-if="photo">
                    <img :src="photo.large">
                </div>

                <div class="flex items-center justify-center w-full" v-if="!photo">
                    <div class="button" @click="$eventer.$emit('add-files', fileUploadName)">
                        <div class="">Upload A Photo</div>
                    </div>
                </div>

            </div>

        </div>

        <file-uploads
            :name="fileUploadName"
            v-model="uploads"
            multiple="false"
            :items="photos"
            type="image"
        ></file-uploads>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';
    import Editor from '@/Components/Editor.vue';
    import FileUploads from '@/Components/FileUploads';

    export default {

        props: [
            'content',
        ],

        mixins: [Feedback],

        components: {
            'editor': Editor,
            'file-uploads': FileUploads,
        },

        data() {
            return {
                uploads: [],
            }
        },

        computed: {
            photos() {
                return this.content.photos;
            },
            photo() {
                return this.content.photos[0];
            },
            fileUploadName() {
                return 'photos-' + this.content.id;
            },
        },

        watch: {

            uploads() {
                this.updatePhotos();
            },

        },

        mounted() {
        },

        methods: {

            updatePhotos: function() {
                this.$lodash.forEach(this.uploads, (upload, index) => {
                    this.addUpload(upload);
                });
            },

            addUpload: function(upload) {

                if (upload.id >= 1) {

                    if (!this.$lodash.find(this.photos, function(u) {
                        return u.file_upload.id == upload.id;
                    })) {

                        let newPhoto = {
                            id: '0.' + this.photos.length,
                            name: '',
                            description: '',
                            alt: '',
                            sort_order: this.photos.length + 1,
                            span: 1,
                            offsetX: 50,
                            offsetY: 50,
                            large: upload.large,
                            file_upload: upload,
                        }

                        this.photos.push(newPhoto);
                    }
                }
            },

            removePhoto: function(photo, index) {

                var answer = confirm('Are you sure you want to delete this photo?');
                if (answer == true) {

                    this.$http.post('/photos/' + photo.id + '/remove').then( response => {

                        this.processSuccess(response);

                        let uploadIndex = this.$lodash.findIndex(this.uploads, {'id': photo.file_upload.id});
                        this.uploads.splice(uploadIndex, 1);
                        this.photos.splice(index, 1);

                    }, function (error) {
                        this.processErrors(error.response);
                    });

                }

            },
            
        },

    }
</script>
