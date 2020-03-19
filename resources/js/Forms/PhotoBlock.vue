<template>

    <div class="mt-8 relative z-2">

        <div class="absolute flex flex-col justify-center items-center" style="left: -50px;" v-if="photos.length">

            <div class="photo-block-icons text-xl" @click="decreaseHeight()"><i class="fas fa-angle-double-up"></i></div>
            <div class="photo-block-icons text-xl" @click="increaseHeight()"><i class="fas fa-angle-double-down"></i></div>

            <div class="photo-block-icons" @click="content.columns < 4 ? content.columns++ : null">
                <div class=""><i class="fas fa-columns"></i></div>
                <div class="pl-1"><i class="fas fa-plus"></i></div>
            </div>

            <div class="photo-block-icons" @click="content.columns > 1 ? content.columns-- : null">
                <div class=""><i class="fas fa-columns"></i></div>
                <div class="pl-1"><i class="fas fa-minus"></i></div>
            </div>

            <div class="photo-block-icons text-xl" @click="content.show_text = !content.show_text">
                <div v-if="content.show_text"><i class="fas fa-align-justify"></i></div>
                <div v-if="!content.show_text" class="text-gray-400"><i class="fas fa-align-justify"></i></div>
            </div>

            <transition name="fade">
                <div class="photo-block-icons text-xl" @click="content.padding = !content.padding" v-if="photos.length > 1">
                    <div v-if="content.padding"><i class="fas fa-border-none"></i></div>
                    <div v-if="!content.padding"><i class="fas fa-border-all"></i></div>
                </div>
            </transition>

        </div>

        <transition-group name="photo-editor" tag="div" class="relative grid" :class="['grid-cols-' + content.columns, content.padding ? 'gap-2' : '']">

            <div v-if="content.show_text" key="text" 
                :style="textPosition"
                class="relative" 
                :class="['col-span-' + content.text_span, textPosition.row, textPosition.column]"
            >

                <div class="text-block flex flex-col justify-center h-full">
                    <div class="h2">
                        <input type="text" v-model="content.header" @change="saveContent()" placeholder="Header" />
                    </div>

                    <editor v-model="content.body" 
                            @input="saveContent()"
                            placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit."
                    ></editor>
                </div>

                <div class="absolute bottom-0 flex text-xl justify-between w-full">

                    <div class="flex">
                        <transition name="slide-icon">
                            <div class="cursor-pointer mx-1" v-if="content.text_order > 1" @click="content.text_order--"><i class="fas fa-arrow-alt-circle-left"></i></div>
                        </transition>

                        <transition name="slide-icon">
                            <div class="cursor-pointer mx-1" v-if="content.text_order < (totalCells + 1)" @click="content.text_order++"><i class="fas fa-arrow-alt-circle-right"></i></div>
                        </transition>
                    </div>

                    <div class="flex">
                        <transition name="slide-icon">
                            <div class="cursor-pointer mx-1" v-if="content.text_span < content.columns" @click="content.text_span++"><i class="fas fa-plus-circle"></i></div>
                        </transition>
                        <transition name="slide-icon">
                            <div class="cursor-pointer mx-1" v-if="content.text_span > 1" @click="content.text_span--"><i class="fas fa-minus-circle"></i></div>
                        </transition>
                    </div>

                </div>

            </div>

            <div class="relative overflow-hidden" 
                :class="'col-span-' + photo.span"
                v-for="(photo, index) in sortedPhotos"
                :key="'photo-' + photo.id"
                :style="{
                    paddingBottom: Math.floor(content.height / photo.span) + '%',
                }"
            >

                <div class="photo">
                    <img :src="photo.small" :style="'object-position: ' + photo.offsetX + '% ' + photo.offsetY + '%;'" />
                </div>

                <div class="absolute right-0 transform -rotate-90 origin-bottom-right w-32">
                    <div class="flex items-center px-2 py-1" style="background-color: rgba(255,255,255,0.5)">
                        <input type="range" v-model="photo.offsetY" min="0" max="100" />
                    </div>
                </div>

                <div class="absolute right-0 w-32">
                    <div class="flex items-center px-2 py-1" style="background-color: rgba(255,255,255,0.5)">
                        <input type="range" v-model="photo.offsetX" min="0" max="100" />
                    </div>
                </div>

                <div class="absolute bottom-0 flex text-white text-xl justify-between w-full">

                    <div class="flex">
                        <transition name="slide-icon">
                            <div class="cursor-pointer mx-1" v-if="photo.sort_order > 1 && photos.length > 1" @click="decreaseOrder(photo)"><i class="fas fa-arrow-alt-circle-left"></i></div>
                        </transition>

                        <transition name="slide-icon">
                            <div class="cursor-pointer mx-1" v-if="photo.sort_order < photos.length && photos.length > 1" @click="increaseOrder(photo)"><i class="fas fa-arrow-alt-circle-right"></i></div>
                        </transition>
                    </div>

                    <div class="flex">
                        <transition name="slide-icon">
                            <div class="cursor-pointer mx-1" v-if="photo.span < content.columns && content.columns > 1" @click="photo.span++"><i class="fas fa-plus-circle"></i></div>
                        </transition>
                        <transition name="slide-icon">
                            <div class="cursor-pointer mx-1" v-if="photo.span > 1 && content.columns > 1" @click="photo.span--"><i class="fas fa-minus-circle"></i></div>
                        </transition>
                    </div>

                </div>

            </div>

        </transition-group>

        <file-uploads
            :name="fileUploadName"
            v-model="uploads"
            multiple="true"
            :items="photos"
            type="image"
        ></file-uploads>

        <div class="flex bg-gray-100 p-2 shadow mt-2">

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
                heights: [ 25, 33, 50, 66, 75, 100 ],
            }
        },

        computed: {
            photos() {
                return this.content.photos;
            },
            sortedPhotos() {
                return this.$lodash.orderBy(this.photos, ['sort_order', 'id'], ['asc', 'asc']);
            },
            photosCount() {
                return this.photos.length;
            },
            fileUploadName() {
                return 'photos-' + this.content.id;
            },
            totalCells() {
                return this.$lodash.sumBy(this.content.photos, 'span') + this.content.text_span;
            },
            textPosition() {

                let row;
                let column;

                row = Math.ceil(this.content.text_order / this.content.columns);

                column = this.content.text_order % this.content.columns;
                if (column === 0) {
                    column = this.content.columns;
                }

                return {
                    row: 'row-start-'+ row,
                    column: 'col-start-' + column,
                };
            }
        },

        watch: {
            photos() {
                this.saveContent();
            },
            uploads() {
                this.updatePhotos();
            },
            photosCount() {
                this.setLayout();
            }
        },

        mounted() {
            if (this.content.id < 1) {
                this.$eventer.$emit('add-files', this.fileUploadName);
            }
        },

        methods: {

            saveContent: _.debounce( function() {
                //this.$emit('save');
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
                            sort_order: this.photos.length + 1,
                            span: 1,
                            offsetX: 50,
                            offsetY: 50,
                            small: upload.small,
                            file_upload: upload,
                        }

                        this.photos.push(newPhoto);
                    }
                }
            },

            increaseHeight: function() {
                let index = this.$lodash.findIndex(this.heights, height => {
                    return this.content.height === height;
                });

                if (index < (this.heights.length - 1)) {
                    this.content.height = this.heights[index + 1];
                }
            },

            decreaseHeight: function() {
                let index = this.$lodash.findIndex(this.heights, height => {
                    return this.content.height === height;
                });

                if (index > 0) {
                    this.content.height = this.heights[index - 1];
                }
            },


            increaseOrder: function(photo) {
                let nextPhoto = this.$lodash.find(this.sortedPhotos, p => {
                    return photo.sort_order + 1 === p.sort_order;
                });

                photo.sort_order++;
                nextPhoto.sort_order--;
            },

            decreaseOrder: function(photo) {
                let prevPhoto = this.$lodash.find(this.sortedPhotos, p => {
                    return photo.sort_order - 1 === p.sort_order;
                });

                photo.sort_order--;
                prevPhoto.sort_order++;
            },

            setLayout: function() {

                if (this.photos.length == 1) {

                    this.content.columns = 1;
                    this.content.height = 33;
                    this.content.padding = false;

                }


            }

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

<style>

@keyframes slide-icon {
    0% {
        opacity: 0;
        max-width: 0;
    }
    100%   {
        opacity: 1;
        max-width: 30px;
    }
}

.slide-icon-enter-active {
    animation: slide-icon var(--transition-time) ease-out;
}

.slide-icon-leave-active {
    animation: slide-icon var(--transition-time) reverse;
}

.photo-editor-move {
  transition: transform var(--transition-time);
}

</style>