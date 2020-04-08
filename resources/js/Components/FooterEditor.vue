<template>

    <div class="shadow mt-8 max-w-6xl absolute z-3 bg-gray-100" v-if="page ? ( page.id > 1 ? true : false ) : false">
        
        <transition name="slide-down">
            <div class="absolute flex justify-center items-center w-full h-full bg-wash z-3" v-if="saving">
                <div class="">
                    <div class="flex text-green-600 bg-gray-100 px-4 py-2 border border-green-200 shadow">
                        <div class="spin"><i class="fas fa-sync-alt"></i></div>
                        <div class="ml-2">Saving</div>
                    </div>
                </div>
            </div>
        </transition>

        <div class="relative flex items-center z-2 mx-4 my-2">

            <div class="">
                <div class="button" @click="$eventer.$emit('add-files', 'footer-fg')">
                    <div class="">Change Footer Foreground</div>
                </div>
            </div>
        
            <div class="">
                <div class="button ml-4" @click="$eventer.$emit('add-files', 'footer-bg')">
                    <div class="">Change Footer background</div>
                </div>
            </div>

            <div class="form pl-2">
                <input type="text" v-model="page.footer_color" @change="$eventer.$emit('save-page')" placeholder="Footer Color 255,255,255" />
            </div>

        </div>

        <file-uploads
            name="footer-fg"
            v-model="fg"
            :multiple="false"
            type="image"
        ></file-uploads>
    
        <file-uploads
            name="footer-bg"
            v-model="bg"
            :multiple="false"
            type="image"
        ></file-uploads>
        
    </div>

</template>

<script>

    import FileUploads from '@/Components/FileUploads';
    import Feedback from '@/Mixins/Feedback';

    export default {

        mixins: [Feedback],

        components: {
            'file-uploads': FileUploads,
        },

        data() {
            return {
                fg: {},
                bg: {},
            }
        },

        computed: {
            page() {
                return this.$store.state.page;
            },
            saving() {
                return this.$store.state.saving.find( save => save === 'page');
            }
        },

        watch: {
            fg() {
                this.page.footer_fg_file_upload = this.fg;
                this.$eventer.$emit('save-page');
            },
            bg() {
                this.page.footer_bg_file_upload = this.bg;
                this.$eventer.$emit('save-page');
            },
        },

        mounted() {
        },

        methods: {
        },

    }
</script>
