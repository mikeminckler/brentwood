<template>

    <div class="mt-8 relative z-2">

        <div class="relative flex mb-2 last:mb-0" v-for="(group, index) in $lodash.chunk(sortedPhotos, 3)">
            <div class="relative mx-1 first:ml-0 last:mr-0"
                v-for="photo in group" 
                :key="'photo-' + photo.id"
                :class="['flex-' + photo.flex]"
                :style="'padding-bottom: ' + photo.height + '%;'"
            >
                <div class="absolute w-full h-full overflow-hidden">
                    <img class="w-full h-full object-cover" :src="photo.small" :style="'object-position: ' + photo.offsetX + '% ' + photo.offsetY + '%;'" />
                </div>

                <div class="absolute left-0">
                    <div class="flex items-center px-2 py-1" style="background-color: rgba(255,255,255,0.5)">
                        <input type="range" v-model="photo.offsetX" min="0" max="100" />
                    </div>
                </div>

                <div class="absolute left-0 transform rotate-90 origin-bottom-left">
                    <div class="transform rotate-180 flex items-center px-2 py-1" style="background-color: rgba(255,255,255,0.5)">
                        <input type="range" v-model="photo.offsetY" min="0" max="100" />
                    </div>
                </div>

            </div>

            <div class="absolute transform rotate-90 origin-bottom-left" style="left: -32px">
                <div class="transform flex items-center px-2 py-1 bg-gray-200">
                    <input type="range" @input="setRowHeight($event, group)" v-model="group[0].height" min="25" max="50" />
                </div>
            </div>

            <div class="absolute bottom-0 flex" style="background-color: rgba(255,255,255,0.5)">
                <div class="mx-2" v-if="group.length === 2" @click="setLayout1(group)"> <img src="/images/image-layout-1.png" class="h-8" /> </div>
                <div class="mx-2" v-if="group.length === 2" @click="setLayout2(group)"> <img src="/images/image-layout-2.png" class="h-8" /> </div>
            </div>

        </div>

        <file-uploads
            :name="fileUploadName"
            v-model="uploads"
            multiple="true"
            :items="photos"
            type="image"
        ></file-uploads>

        <div class="flex bg-gray-100 p-2 shadow">

            <div class="button" @click="$eventer.$emit('add-files', fileUploadName)">
                <div class="">Upload Files</div>
            </div>

        </div>

    </div>

</template>

<script>

    import Editor from '@/Components/Editor.vue';
    import FileUploads from '@/Components/FileUploads';

    export default {

        props: [
            'content',
        ],

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
            sortedPhotos() {
                return this.$lodash.orderBy(this.photos, ['sort_order', 'id'], ['asc', 'asc']);
            },
            fileUploadName() {
                return 'photos-' + this.content.id;
            },
        },

        watch: {
            photos() {
                this.saveContent();
            },
            uploads() {
                this.updatePhotos();
            }
        },

        mounted() {
            if (this.content.id < 1) {
                this.$eventer.$emit('add-files', this.fileUploadName);
            }
        },

        methods: {

            saveContent: _.debounce( function() {
                this.$emit('save');
            }, 1000),

            /*
            setPhotos: function() {

                if (this.value) {

                    if (this.$lodash.isArray(this.value)) {
                        this.photos = this.value;
                    } else if (this.$lodash.has(this.value, 'id')) {
                        this.photos = [this.value];
                    }
                }

            },
            */

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
                            sort_order: this.photos.length,
                            flex: 1,
                            height: this.photos.length % 3 ? 0 : 33,
                            offsetX: 50,
                            offsetY: 50,
                            small: upload.small,
                            file_upload: upload,
                        }

                        this.photos.push(newPhoto);
                    }
                }
            },

            setRowHeight: function(event, group) {
                this.$lodash.each(group, photo => {
                    photo.height = event.target.value;
                });
            },

            setLayout1: function(group) {
                group[0].flex = 1;
                group[1].flex = 2;
            },

            setLayout2: function(group) {
                group[0].flex = 2;
                group[1].flex = 1;
            },

            /*
            removePhoto: function(photo, index) {

                if (photo.id  >= 1) {

                    var answer = confirm('Are you sure you want to delete this photo?');
                    if (answer == true) {

                        this.$http.post('/photos/' + photo.id + '/destroy').then( response => {

                            this.processSuccess(response);

                            if (this.multiple) {
                                let uploadIndex = this.$lodash.findIndex(this.uploads, {'id': photo.file_upload.id});
                                this.uploads.splice(uploadIndex, 1);
                            } else {
                                this.uploads = {};
                            }
                            this.photos.splice(index, 1);

                        }, function (error) {
                            this.processErrors(error.response);
                        });

                    }

                } else {

                    if (this.multiple) {
                        let uploadIndex = this.$lodash.findIndex(this.uploads, {'id': photo.file_upload.id});
                        this.uploads.splice(uploadIndex, 1);
                    } else {
                        this.uploads = {};
                    }
                    this.photos.splice(index, 1);

                }
            },
            */
        },

    }
</script>
