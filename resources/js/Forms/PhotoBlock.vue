<template>

    <div class="relative z-2">

        <div class="absolute flex flex-col justify-center items-center" style="left: -50px;" v-if="photos.length">

            <div class="content-element-icons text-xl" @click="decreaseHeight()"><i class="fas fa-angle-double-up"></i></div>
            <div class="content-element-icons text-xl" @click="increaseHeight()"><i class="fas fa-angle-double-down"></i></div>

            <div class="content-element-icons" @click="content.columns < 4 ? content.columns++ : null">
                <div class=""><i class="fas fa-columns"></i></div>
                <div class="pl-1"><i class="fas fa-plus"></i></div>
            </div>

            <div class="content-element-icons" @click="content.columns > 1 ? content.columns-- : null">
                <div class=""><i class="fas fa-columns"></i></div>
                <div class="pl-1"><i class="fas fa-minus"></i></div>
            </div>

            <div class="content-element-icons text-xl" @click="content.show_text = !content.show_text">
                <div v-if="content.show_text"><i class="fas fa-align-justify"></i></div>
                <div v-if="!content.show_text" class="text-gray-400"><i class="fas fa-align-justify"></i></div>
            </div>

            <transition name="fade">
                <div class="content-element-icons text-xl" @click="content.padding = !content.padding" v-if="photos.length > 1">
                    <div v-if="content.padding"><i class="fas fa-border-none"></i></div>
                    <div v-if="!content.padding"><i class="fas fa-border-all"></i></div>
                </div>
            </transition>

        </div>

        <transition-group name="photo-editor" tag="div" class="relative grid" :class="['grid-cols-' + content.columns, content.padding ? (content.columns === 3 ? 'row-gap-2' : 'gap-2' ) : '']">

            <div v-if="content.show_text" key="text" 
                class="relative" 
                :class="['col-span-' + content.text_span, textPosition.row, textPosition.column, content.text_style ? 'photo-block-text-' + content.text_style : '']"
            >

                <div class="text-block flex flex-col justify-center h-full">
                    <div class="h2">
                        <input type="text" v-model="content.header" placeholder="Header" />
                    </div>

                    <editor v-model="content.body" 
                            placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit."
                    ></editor>
                </div>

                <div class="absolute bottom-0 flex text-xl items-end">

                    <div class="">
                        <div class="w-6 h-6 bg-white" @click="content.text_style = ''"></div>
                        <div class="w-6 h-6 bg-gray-200" @click="content.text_style = 'gray'"></div>
                        <div class="w-6 h-6 bg-blue-200" @click="content.text_style = 'blue'"></div>
                    </div>

                    <div class="photo-icons flex ml-4">
                        <div class="cursor-pointer mx-1" v-if="content.text_order > 1" @click="content.text_order--"><i class="fas fa-arrow-alt-circle-left"></i></div>
                        <div class="cursor-pointer mx-1" v-if="content.text_order < (totalCells + 1)" @click="content.text_order++"><i class="fas fa-arrow-alt-circle-right"></i></div>
                        <div class="cursor-pointer mx-1" v-if="content.text_span < content.columns" @click="content.text_span++"><i class="fas fa-plus-circle"></i></div>
                        <div class="cursor-pointer mx-1" v-if="content.text_span > 1" @click="content.text_span--"><i class="fas fa-minus-circle"></i></div>
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

                <div v-if="index === (sortedPhotos.length - 1)" class="h-1 bg-gray-200 opacity-50 w-full absolute bottom-0 z-5"></div>

                <div class="photo" :class="photo.fill ? 'fill' : 'fit'" v-if="photo">
                    <img :src="photo.large" :style="'object-position: ' + photo.offsetX + '% ' + photo.offsetY + '%;'" />
                </div>

                <photo-controls :photo="photo"
                    :span="true"
                    :sort="true"
                    :content="content"
                    :photos="photos"
                    @sortUp="sortUp(photo)"
                    @sortDown="sortDown(photo)"
                    @remove="removePhoto(photo, index)"
                ></photo-controls>

            </div>

        </transition-group>

        <file-uploads
            :name="fileUploadName"
            v-model="uploads"
            multiple="true"
            :items="photos"
            type="image"
        ></file-uploads>

        <div class="flex bg-gray-200 p-2 shadow mt-4">

            <div class="button" @click="$eventer.$emit('add-files', fileUploadName)">
                <div class="">Upload Files</div>
            </div>

            <div class="button mx-1" v-if="photos.length === 1" @click="setText1()"><img src="/images/text1.png" /></div>
            <div class="button mx-1" v-if="photos.length === 1" @click="setText2()"><img src="/images/text2.png" /></div>
            <div class="button mx-1" v-if="photos.length === 1" @click="setText3()"><img src="/images/text3.png" /></div>

            <div class="button mx-1" v-if="photos.length === 2" @click="setText4()"><img src="/images/text1.png" /></div>
            <div class="button mx-1" v-if="photos.length === 2" @click="setText5()"><img src="/images/text2.png" /></div>
            <div class="button mx-1" v-if="photos.length === 2" @click="setText6()"><img src="/images/text3.png" /></div>
            <div class="button mx-1" v-if="photos.length === 2" @click="setText7()"><img src="/images/text3.png" /></div>

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
                heights: [ 25, 33, 40, 50, 66, 75, 100 ],
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
            if (this.content.id < 1) {
                this.$eventer.$emit('add-files', this.fileUploadName);
            }
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
                        this.setText4();
                    }
                } else {

                    if (this.photos.length === 1) {
                        this.content.columns = 1;
                        this.content.height = 33;
                        this.content.padding = false;
                    }

                    if (this.photos.length === 2) {

                        this.content.columns = 2;
                        this.content.height = 50;
                        this.content.padding = false;

                    }

                    if (this.photos.length === 3) {
                        this.content.columns = 3;
                        this.content.height = 50;
                        this.content.padding = false;
                    }
                }

            },

            setText1: function() {

                if (this.photos.length === 1) {
                    this.content.columns = 3;
                    this.content.height = 100;
                    this.content.padding = false;
                    this.content.show_text = true;
                    this.content.text_order = 1;
                    this.content.text_span = 1;
                    this.sortedPhotos[0].span = 2;
                }
                
            },

            setText2: function() {

                if (this.photos.length === 1) {
                    this.content.columns = 3;
                    this.content.height = 100;
                    this.content.padding = false;
                    this.content.show_text = true;
                    this.content.text_order = 3;
                    this.content.text_span = 2;
                    this.sortedPhotos[0].span = 2;
                }
                
            },

            setText3: function() {

                if (this.photos.length === 1) {
                    this.content.columns = 3;
                    this.content.height = 100;
                    this.content.padding = false;
                    this.content.show_text = true;
                    this.content.text_order = 2;
                    this.content.text_span = 2;
                    this.sortedPhotos[0].span = 1;
                }
                
            },

            setText4: function() {

                if (this.photos.length === 2) {
                    this.content.columns = 3;
                    this.content.height = 100;
                    this.content.padding = true;
                    this.content.show_text = true;
                    this.content.text_order = 3;
                    this.content.text_span = 1;
                    this.sortedPhotos[0].sort_order = 1;
                    this.sortedPhotos[0].span = 2;
                    this.sortedPhotos[1].sort_order = 2;
                    this.sortedPhotos[1].span = 3;
                }

            },

            setText5: function() {

                if (this.photos.length === 2) {
                    this.content.columns = 3;
                    this.content.height = 100;
                    this.content.padding = true;
                    this.content.show_text = true;
                    this.content.text_order = 1;
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
                    this.content.padding = true;
                    this.content.show_text = true;
                    this.content.text_order = 2;
                    this.content.text_span = 1;
                    this.sortedPhotos[0].sort_order = 1;
                    this.sortedPhotos[0].span = 1;
                    this.sortedPhotos[1].sort_order = 2;
                    this.sortedPhotos[1].span = 1;
                }

            },

            setText7: function() {

                if (this.photos.length === 2) {
                    this.content.columns = 3;
                    this.content.height = 100;
                    this.content.padding = true;
                    this.content.show_text = true;
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
