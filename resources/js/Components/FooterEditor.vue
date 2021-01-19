<template>

    <div class="absolute w-full flex justify-center h-0 overflow-visible">
        <div class="relative max-w-6xl flex w-full z-5">

            <div class="flex-1"></div>
            <div class="flex-2 flex justify-center">
                <div class="" v-if="page">
                    
                    <transition name="slide-down">
                        <div class="absolute flex justify-center items-center w-full h-full bg-white bg-opacity-75 z-3" v-if="saving">
                            <div class="flex text-green-600 bg-gray-100 px-4 py-2 border border-green-200 shadow">
                                <div class="spin"><i class="fas fa-sync-alt"></i></div>
                                <div class="ml-2">Saving</div>
                            </div>
                        </div>
                    </transition>

                    <div class="relative flex items-center z-2 mx-4 my-2">

                        <div class="">
                            <div class="button" @click="$eventer.$emit('add-files', 'footer-bg')">
                                <div class="">Change Background</div>
                            </div>
                        </div>

                        <div class="pl-2">
                            <div class="button" @click="$eventer.$emit('add-files', 'footer-fg')">
                                <div class="">Change Foreground</div>
                            </div>
                        </div>

                        <div class="form pl-2">
                            <input type="text" v-model="page.footer_color" @change="$eventer.$emit('save-page')" placeholder="Footer Color 255,255,255" />
                        </div>

                    </div>

                    <file-uploads
                        name="footer-fg"
                        v-model="fgUpload"
                        :multiple="false"
                        type="image"
                    ></file-uploads>
                
                    <file-uploads
                        name="footer-bg"
                        v-model="bgUpload"
                        :multiple="false"
                        type="image"
                    ></file-uploads>
                    
                </div>
            </div>
        </div>

        <div class="absolute w-full" style="min-height: 700px;">
            <div class="absolute z-1 w-full h-full" :style="backgroundColor"></div>
            <div class="absolute w-full h-full">

                <picture class="w-full h-full z-2 absolute" v-if="page.footer_fg_photo ? page.footer_fg_photo.large ? true : false : false">
                    <source media="(min-width: 900px)" :srcset="page.footer_fg_photo.large + '.webp'" type="image/webp" >
                    <source media="(min-width: 400px)" srcset="page.footer_fg_photo.medium + '.webp'" type="image/webp" >
                    <source :srcset="page.footer_fg_photo.small + '.webp'" type="image/webp" >
                    <img class="w-full h-full object-cover"
                        :srcset="page.footer_fg_photo.small + ' 400w, ' + page.footer_fg_photo.medium + ' 900w, ' + page.footer_fg_photo.large + ' 1152w'"
                        :src="page.footer_fg_photo.large"
                         :type="'image/' + page.footer_fg_photo.file_upload.extension">
                </picture>

                <picture class="w-full h-full" v-if="page.footer_bg_photo ? page.footer_bg_photo.large ? true : false : false">
                    <source media="(min-width: 900px)" :srcset="page.footer_bg_photo.large + '.webp'" type="image/webp" >
                    <source media="(min-width: 400px)" srcset="page.footer_bg_photo.medium + '.webp'" type="image/webp" >
                    <source :srcset="page.footer_bg_photo.small + '.webp'" type="image/webp" >
                    <img class="w-full h-full object-cover"
                        :srcset="page.footer_bg_photo.small + ' 400w, ' + page.footer_bg_photo.medium + ' 900w, ' + page.footer_bg_photo.large + ' 1152w'"
                        :src="page.footer_bg_photo.large"
                         :type="'image/' + page.footer_bg_photo.file_upload.extension">
                </picture>

            </div>
        </div>
    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';

    export default {

        mixins: [Feedback],

        components: {
            'file-uploads': () => import(/* webpackChunkName: "file-uploads" */ '@/Components/FileUploads.vue'),
        },

        data() {
            return {
                fgUpload: null,
                bgUpload: null,
            }
        },

        computed: {
            page() {
                return this.$store.state.page;
            },
            saving() {
                return this.$store.state.saving.find( save => save === 'page');
            },
            backgroundColor() {
                let color = this.page.footer_color ? this.page.footer_color : '218,241,250';
                return 'background-image: linear-gradient(180deg, rgba(' + color + ',1), rgba(' + color + ',0);"';
            }
        },

        watch: {
            fgUpload() {

                if (this.fgUpload.id >= 1) {

                    let newPhoto = {
                        id: '0',
                        name: this.fgUpload.filename,
                        description: '',
                        alt: '',
                        sort_order: 1,
                        span: 1,
                        offsetX: 50,
                        offsetY: 50,
                        fill: true,
                        large: null,
                        file_upload: this.fgUpload,
                    }

                    this.page.footer_fg_photo = newPhoto;
                    console.log('SAVE PAGE FROM FOOTER FG');
                    this.$eventer.$emit('save-page');
                }
            },

            bgUpload() {

                if (this.bgUpload.id >= 1) {

                    let newPhoto = {
                        id: '0',
                        name: this.bgUpload.filename,
                        description: '',
                        alt: '',
                        sort_order: 1,
                        span: 1,
                        offsetX: 50,
                        offsetY: 50,
                        fill: true,
                        large: null,
                        file_upload: this.bgUpload,
                    }

                    this.page.footer_bg_photo = newPhoto;
                    console.log('SAVE PAGE FROM FOOTER BG');
                    this.$eventer.$emit('save-page');
                }
            },

        },

        mounted() {
        },

        methods: {
        },

    }
</script>
