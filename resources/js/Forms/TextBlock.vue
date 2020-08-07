<template>

    <div class="flex relative" :class="content.full_width ? 'bg-white z-3' : ( photo && content.style ? 'text-style-' + content.style : '' )">

        <div class="flex-1 relative flex flex-col" v-if="!content.full_width">

            <div class="flex z-3 w-full items-center justify-center flex flex-col items-center justify-center h-full">

                <stat v-if="showStat || content.stat_number || content.stat_name" :model="content" :photo="photo"></stat>

                <div class="button mb-2" @click="showStat = true" v-if="!showStat && !content.stat_number && !content.stat_name">
                    <div class="">Add Statistic</div>
                </div>

                <div class="" v-if="!photo">
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

            <div class="absolute w-full h-full">
                <photo-controls :photo="photo" 
                    :fill="1" 
                    :content="content"
                    :photos="photos"
                    @remove="removePhoto(photo, 0)"
                     class="z-4"
                ></photo-controls>

                <div class="photo z-2" :class="photo.fill ? 'fill' : 'fit'" v-if="photo">
                    <img :src="photo.large" :style="'object-position: ' + photo.offsetX + '% ' + photo.offsetY + '%;'">
                </div>

            </div>

        </div>

        <div class="flex-2 flex justify-center relative" :class="!photo && content.style ? 'text-style-' + content.style : ''">

            <div :class="content.full_width ? 'px-12 py-8' : 'text-block'">
                <div class="">
                    <input :class="first ? 'h1' : 'h2'" @blur="content.header = content.header + ' '" type="text" v-model="content.header" placeholder="Header" />
                </div>

                <editor v-model="content.body" 
                        :class="content.full_width ? 'columns-2' : ''"
                    placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit."
                ></editor>

                <div v-if="first" class="h-1 w-16 bg-gray-400 my-4"></div>

                <div class="flex">
                    <div class="w-6 h-6 bg-transparent cursor-pointer" @click="content.style = ''"><i class="fas fa-ban"></i></div>
                    <div class="w-6 h-6 bg-white cursor-pointer" @click="content.style = 'white'"></div>
                    <div class="w-6 h-6 bg-gray-200 cursor-pointer" @click="content.style = 'gray'"></div>
                    <div class="w-6 h-6 bg-blue-200 cursor-pointer" @click="content.style = 'blue'"></div>
                    <checkbox-input v-model="content.full_width" label="Full Width"></checkbox-input> 
                </div>
            </div>

        </div>

    </div>

</template>

<script>

    import Photos from '@/Mixins/Photos';
    import Feedback from '@/Mixins/Feedback';

    export default {

        props: [ 'content', 'uuid', 'first'],
        mixins: [ Photos, Feedback ],

        components: {
            'editor': () => import(/* webpackChunkName: "editor" */ '@/Components/Editor.vue'),
            'file-uploads': () => import(/* webpackChunkName: "file-uploads" */ '@/Components/FileUploads.vue'),
            'photo-controls': () => import(/* webpackChunkName: "photo-controls" */ '@/Components/PhotoControls.vue'),
            'stat': () => import(/* webpackChunkName: "stat" */ '@/Components/Stat.vue'),
            'checkbox-input': () => import(/* webpackChunkName: "checkbox-input" */ '@/Components/CheckboxInput.vue'),
        },

        data() {
            return {
                multiplePhotos: false,
                showStat: false,
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
