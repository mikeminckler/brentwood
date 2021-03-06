<template>

    <div class="relative z-2" id="form-photo-block">

        <div class="absolute flex flex-col justify-center items-center" style="left: -50px;" v-if="photos.length">

            <div class="button-icon" title="Increase Row Height" @click="decreaseHeight()"><i class="fas fa-angle-double-up"></i></div>
            <div class="button-icon mt-2" title="Descrease Row Height" @click="increaseHeight()"><i class="fas fa-angle-double-down"></i></div>

            <div class="button-icon mt-2" @click="content.columns < 4 ? content.columns++ : null" title="Add Column">
                <div class=""><i class="fas fa-plus"></i></div>
            </div>

            <div class="button-icon mt-2" @click="content.columns > 1 ? content.columns-- : null" title="Remove Column">
                <div class=""><i class="fas fa-minus"></i></div>
            </div>

            <div class="button-icon toggle mt-2" :class="content.show_text ? 'active' : ''" @click="content.show_text = !content.show_text" :title="content.show_text ? 'Hide Text' : 'Show Text'">
                <div v-if="content.show_text"><i class="fas fa-align-justify"></i></div>
                <div v-if="!content.show_text" class="text-gray-400"><i class="fas fa-align-justify"></i></div>
            </div>

            <div class="button-icon toggle mt-2" :class="content.padding ? 'active' : ''" @click="content.padding = !content.padding" v-if="photos.length > 1" :title="content.padding ? 'Remove Padding' : 'Add Padding'">
                <div v-if="content.padding"><i class="fas fa-border-none"></i></div>
                <div v-if="!content.padding"><i class="fas fa-border-all"></i></div>
            </div>

        </div>

        <transition-group name="photo-editor" 
            tag="div" 
            class="relative grid" 
            :class="['grid-cols-' + content.columns, content.padding ? (content.columns === 3 ? 'gap-y-2' : 'gap-2' ) : '']" 
            :style="photosCount === 0 ? 'min-height: 300px' : ''"
        >

            <div class="absolute w-full h-full flex items-center justify-center bg-gray-200" key="no-photos" v-if="photosCount === 0">
                <div class="button" @click="$eventer.$emit('add-files', fileUploadName)">
                    <div class="">Add Photos</div>
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

                <div v-if="index === (sortedPhotos.length - 1)" class="h-1 bg-gray-200 opacity-50 w-full absolute bottom-0 z-5"></div>

                <div class="photo" :class="photo.fill ? 'fill' : 'fit'" v-if="photo">
                    <img :src="photo.large" :style="'object-position: ' + photo.offsetX + '% ' + photo.offsetY + '%;'" v-if="photo.large" />
                    <div class="flex items-center justify-center bg-gray-200 h-full relative z-3" v-if="!photo.large">
                        <div class="flex bg-gray-100 text-green-500 px-2 py-1">
                            <div class="spin"><i class="fas fa-sync-alt"></i></div>
                            <div class="ml-1">Processing Image</div>
                        </div>
                    </div>
                </div>

                <photo-controls :photo="photo"
                    v-if="photo.id >= 1"
                    :span="1"
                    :sort="1"
                    :content="content"
                    :photos="photos"
                    @sortUp="sortUp(photo)"
                    @sortDown="sortDown(photo)"
                    @remove="removePhoto(photo, index)"
                    :stat="true"
                ></photo-controls>

            </div>

            <div v-if="content.show_text" key="text" 
                class="relative py-4 flex justify-center" 
                :class="['col-span-' + content.text_span, textPosition.row, textPosition.column, content.text_style ? 'text-style-' + content.text_style : '']"
            >

                <div class="">

                    <div class="text-block flex flex-col justify-center h-full">
                        <div class="">
                            <input class="h2" type="text" @blur="saveContent()" v-model="content.header" placeholder="Header" />
                        </div>

                        <editor v-model="content.body" 
                                placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit."
                                @blur="saveContent"
                        ></editor>
                    </div>

                    <div class="absolute bottom-0 flex text-xl items-end">

                        <div class="">
                            <div class="w-6 h-6 bg-transparent cursor-pointer flex items-center justify-center p-1" title="Background Transparent" @click="content.text_style = ''"><i class="fas fa-ban"></i></div>
                            <div class="w-6 h-6 bg-white" title="Background White" @click="content.text_style = 'white'"></div>
                            <div class="w-6 h-6 bg-gray-200" title="Background Grey" @click="content.text_style = 'gray'"></div>
                            <div class="w-6 h-6 bg-blue-200" title="Background Blue" @click="content.text_style = 'blue'"></div>
                        </div>

                        <div class="flex ml-4">
                            <div class="cursor-pointer mx-1" v-if="content.text_order > 1" title="Move Left" @click="content.text_order--"><i class="fas fa-arrow-alt-circle-left"></i></div>
                            <div class="cursor-pointer mx-1" v-if="content.text_order < (totalCells + 1)" title="Move Right" @click="content.text_order++"><i class="fas fa-arrow-alt-circle-right"></i></div>
                            <div class="cursor-pointer mx-1" v-if="content.text_span < content.columns" title="Increase Width" @click="content.text_span++"><i class="fas fa-plus-circle"></i></div>
                            <div class="cursor-pointer mx-1" v-if="content.text_span > 1" title="Descrease Width" @click="content.text_span--"><i class="fas fa-minus-circle"></i></div>
                        </div>

                    </div>
                </div>

            </div>

        </transition-group>

        <div class="flex bg-gray-200 p-2 shadow mt-4 justify-center" v-if="photosCount > 0">

            <div class="button" @click="$eventer.$emit('add-files', fileUploadName)">
                <div class="icon"><i class="fas fa-file-image"></i></div>
                <div class="pl-2">Add More Photos</div>
            </div>

        </div>

        <file-uploads
            :name="fileUploadName"
            v-model="uploads"
            :multiple="multiplePhotos"
            :items="photos"
            type="image"
        ></file-uploads>

        <div class="flex mt-8" v-if="contentElementIndex === 0">
            <div class="flex-1"></div>
            <div class="flex-2 flex justify-center">
                <div class="text-block">
                    <page-attributes></page-attributes>
                </div>
            </div>
        </div>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';
    import Photos from '@/Mixins/Photos';
    import SaveContent from '@/Mixins/SaveContent';

    export default {

        props: [ 'content', 'uuid', 'contentElementIndex' ],

        mixins: [Feedback, Photos, SaveContent ],

        components: {
            'editor': () => import(/* webpackChunkName: "editor" */ '@/Components/Editor.vue'),
            'file-uploads': () => import(/* webpackChunkName: "file-uploads" */ '@/Components/FileUploads.vue'),
            'photo-controls': () => import(/* webpackChunkName: "photo-controls" */ '@/Components/PhotoControls.vue'),
            'page-attributes': () => import(/* webpackChunkName: "page-attributes" */ '@/Forms/PageAttributes.vue'),
        },

        data() {
            return {
                heights: [ 25, 33, 40, 50, 66, 75, 100, 150, 200 ],
                multiplePhotos: true,
            }
        },

        computed: {
            sortedPhotos() {
                return this.$lodash.orderBy(this.photos, ['sort_order', 'id'], ['asc', 'asc']);
            },
            photosCount() {
                return this.photos.length;
            },
            totalCells() {
                return this.$lodash.sumBy(this.content.photos, 'span') + this.content.text_span;
            },
            showText() {
                return this.content.show_text;
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

            photosCount() {
                this.setLayout();
            },

            showText() {
                this.setLayout();
            }
        },

        mounted() {
            //this.$eventer.$emit('add-files', this.fileUploadName);
        },

        methods: {


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

            sortDown: function(photo) {

                let currentIndex = this.$lodash.findIndex(this.sortedPhotos, p => {
                    return photo.id === p.id;
                });

                let nextPhoto = this.sortedPhotos[currentIndex + 1];

                if (nextPhoto) {
                    nextPhoto.sort_order = photo.sort_order;
                }

                photo.sort_order++;
            },

            sortUp: function(photo) {

                let currentIndex = this.$lodash.findIndex(this.sortedPhotos, p => {
                    return photo.id === p.id;
                });

                let prevPhoto = this.sortedPhotos[currentIndex - 1];

                if (prevPhoto) {
                    prevPhoto.sort_order = photo.sort_order;
                }

                photo.sort_order--;
            },

            setLayout: function() {

                if (this.showText) {
                    if (this.photos.length === 1) {
                        this.setText1();
                    }

                    if (this.photos.length === 2) {
                        this.setText5();
                    }
                } else {

                    if (this.photos.length === 1) {
                        this.content.columns = 1;
                        this.content.height = 33;
                        this.content.padding = 0;
                    }

                    if (this.photos.length === 2) {

                        this.content.columns = 2;
                        this.content.height = 50;
                        this.content.padding = 0;

                    }

                    if (this.photos.length === 3) {
                        this.content.columns = 3;
                        this.content.height = 50;
                        this.content.padding = 0;
                    }
                }

            },

            setText1: function() {

                if (this.photos.length === 1) {
                    this.content.columns = 3;
                    this.content.height = 100;
                    this.content.padding = 0;
                    this.content.show_text = 1;
                    this.content.text_order = 1;
                    this.content.text_span = 1;
                    this.sortedPhotos[0].span = 2;
                }
                
            },

            setText2: function() {

                if (this.photos.length === 1) {
                    this.content.columns = 3;
                    this.content.height = 100;
                    this.content.padding = 0;
                    this.content.show_text = 1;
                    this.content.text_order = 3;
                    this.content.text_span = 2;
                    this.sortedPhotos[0].span = 2;
                }
                
            },

            setText3: function() {

                if (this.photos.length === 1) {
                    this.content.columns = 3;
                    this.content.height = 100;
                    this.content.padding = 0;
                    this.content.show_text = 1;
                    this.content.text_order = 2;
                    this.content.text_span = 2;
                    this.sortedPhotos[0].span = 1;
                }
                
            },

            setText4: function() {

                if (this.photos.length === 1) {
                    this.content.columns = 3;
                    this.content.height = 100;
                    this.content.padding = 0;
                    this.content.show_text = 1;
                    this.content.text_order = 1;
                    this.content.text_span = 2;
                    this.content.text_style = 'white';
                    this.sortedPhotos[0].span = 1;
                }
                
            },

            setText5: function() {

                if (this.photos.length === 2) {
                    this.content.columns = 3;
                    this.content.height = 100;
                    this.content.padding = 0;
                    this.content.show_text = 1;
                    this.content.text_order = 3;
                    this.content.text_span = 1;
                    this.sortedPhotos[0].sort_order = 1;
                    this.sortedPhotos[0].span = 2;
                    this.sortedPhotos[1].sort_order = 2;
                    this.sortedPhotos[1].span = 3;
                }

            },

            setText6: function() {

                if (this.photos.length === 2) {
                    this.content.columns = 3;
                    this.content.height = 100;
                    this.content.padding = 0;
                    this.content.show_text = 1;
                    this.content.text_order = 1;
                    this.content.text_span = 1;
                    this.sortedPhotos[0].sort_order = 1;
                    this.sortedPhotos[0].span = 2;
                    this.sortedPhotos[1].sort_order = 2;
                    this.sortedPhotos[1].span = 3;
                }

            },

            setText7: function() {

                if (this.photos.length === 2) {
                    this.content.columns = 3;
                    this.content.height = 100;
                    this.content.padding = 0;
                    this.content.show_text = 1;
                    this.content.text_order = 2;
                    this.content.text_span = 1;
                    this.sortedPhotos[0].sort_order = 1;
                    this.sortedPhotos[0].span = 1;
                    this.sortedPhotos[1].sort_order = 2;
                    this.sortedPhotos[1].span = 1;
                }

            },

            setText8: function() {

                if (this.photos.length === 2) {
                    this.content.columns = 3;
                    this.content.height = 100;
                    this.content.padding = 0;
                    this.content.show_text = 1;
                    this.content.text_order = 2;
                    this.content.text_span = 2;
                    this.sortedPhotos[0].sort_order = 1;
                    this.sortedPhotos[0].span = 1;
                    this.sortedPhotos[1].sort_order = 2;
                    this.sortedPhotos[1].span = 3;
                }

            },

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
