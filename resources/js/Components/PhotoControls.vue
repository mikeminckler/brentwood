<template>

    <div class="photo-icons text-white" v-if="photo">

        <div class="absolute bottom-0 w-full z-3 text-gray-600 max-w-lg" v-if="showLinkMenu">

            <div class="text-lg hover:text-gray-800 absolute top-0 right-0 -mt-2 -mr-2 z-3" @click="hideLinkMenu()"><i class="fas fa-times-circle"></i></div>

            <div class="flex items-center form relative z-2">
                <input class="remove" type="text" v-model="photo.link" placeholder="https://www.brentwood.bc.ca/" ref="linkInput" @keyup.esc="hideLinkMenu" />
                <div v-if="photo.link" class="mr-2 text-lg hover:text-gray-800 absolute right-0" @click="setLink(null)"><i class="fas fa-times-circle"></i></div>
            </div>

            <page-tree  
                max-height="200px"
                :emit-event="true" 
                @selected="setLink($event)" 
                :show-content-elements="true" 
                :expanded="false"
            ></page-tree>
        </div>

        <stat v-if="stat && (showStat || photo.stat_number || photo.stat_name)" :model="photo" :photo="photo"></stat>

        <div class="relative">
            <div class="absolute right-0 bottom-0 transform rotate-90 origin-top-right w-32">
                <div class="flex items-center px-2 py-1">
                    <input type="range" v-model="photo.offsetY" min="0" max="100" class="outline-none" />
                </div>
            </div>

            <div class="absolute right-0 bottom-0 w-32">
                <div class="flex items-center px-2 py-1">
                    <input type="range" v-model="photo.offsetX" min="0" max="100" class="outline-none" />
                </div>
            </div>
        </div>

        <div class="px-2 mb-1 text-white">
            <input type="text" placeholder="caption" v-model="photo.description" style="text-shadow: 1px 1px 0px #000000" />
        </div>

        <div class="flex items-center bg-translucent pb-2 pt-1">
            <div class="flex flex-1 px-2 items-center">
                <div class="cursor-pointer" @click="photo.fill = !photo.fill" v-if="fill">
                    <div v-if="photo.fill"><i class="fas fa-expand"></i></div>
                    <div v-if="!photo.fill"><i class="fas fa-compress"></i></div>
                </div>

                <div class="cursor-pointer mx-1" v-if="photo.sort_order > 1 && photos.length > 1 && sort" @click="$emit('sortUp')"><i class="fas fa-arrow-alt-circle-left"></i></div>
                <div class="cursor-pointer mx-1" v-if="photo.sort_order < photos.length && photos.length > 1 && sort" @click="$emit('sortDown')"><i class="fas fa-arrow-alt-circle-right"></i></div>
                <div class="cursor-pointer mx-1" v-if="photo.span < content.columns && content.columns > 1 && span" @click="photo.span++"><i class="fas fa-plus-circle"></i></div>
                <div class="cursor-pointer mx-1" v-if="photo.span > 1 && content.columns > 1 && span" @click="photo.span--"><i class="fas fa-minus-circle"></i></div>
            </div>
            <div class="mx-2 cursor-pointer" v-if="photo.id >= 1" @click="showStat = !showStat"><i class="fas fa-align-justify"></i></div>
            <div class="mx-2 cursor-pointer" v-if="photo.id >= 1" @click="$eventer.$emit('add-files', fileUploadName)"><i class="fas fa-edit"></i></div>
            <div class="mx-2 cursor-pointer" v-if="photo.id >= 1" @click="showLinkMenu = !showLinkMenu"><i class="fas fa-link"></i></div>
            <div class="mx-2 remove-icon" @click="$emit('remove')"><i class="fas fa-times"></i></div>
        </div>

        <file-uploads
            :name="fileUploadName"
            v-model="uploads"
            :multiple="false"
            :items="photos"
            type="image"
        ></file-uploads>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';

    export default {

        mixins: [Feedback],
        props: ['photo', 'fill', 'sort', 'span', 'content', 'photos', 'stat'],

        components: {
            'file-uploads': () => import(/* webpackChunkName: "file-uploads" */ '@/Components/FileUploads.vue'),
            'stat': () => import(/* webpackChunkName: "stat" */ '@/Components/Stat.vue'),
            'page-tree': () => import(/* webpackChunkName: "page-tree" */ '@/Components/PageTree.vue'),
        },

        data() {
            return {
                uploads: [],
                showStat: false,
                showLinkMenu: false,
            }
        },

        computed: {
            fileUploadName() {
                return 'photo-' + this.photo.id;
            },
        },

        watch: {

            uploads() {
                this.updatePhoto();
            },

        },

        methods: {
            
            updatePhoto: function() {
                this.photo.file_upload = this.uploads;
                this.photo.name = this.uploads.name;
                this.photo.large = this.uploads.large;
            },

            setLink: function(link) {
                this.photo.link = link;
                this.showLinkMenu = false;
            },

            hideLinkMenu: function() {
                this.showLinkMenu = false;
            }
        },
    }
</script>
