<template>

    <div class="px-4 py-2 shadow max-w-6xl absolute z-5 bg-gray-100" v-if="page ? ( page.id > 1 ? true : false ) : false">
        
        <div class="flex items-center">

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
                <input type="text" v-model="page.footer_color" placeholder="Footer Color 255,255,255" />
            </div>

        </div>

        <file-uploads
            name="footer-fg"
            v-model="page.footer_fg_file_upload"
            :multiple="false"
            type="image"
        ></file-uploads>
    
        <file-uploads
            name="footer-bg"
            v-model="page.footer_bg_file_upload"
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
            
            }
        },

        computed: {
            page() {
                return this.$store.state.page;
            },
            fg() {
                return this.page.footer_fg_file_upload ? this.page.footer_fg_file_upload.id : null;
            },
            bg() {
                return this.page.footer_bg_file_upload ? this.page.footer_bg_file_upload.id : null;
            },
        },

        watch: {
            fg() {
                this.$eventer.$emit('save-page');
            },
            bg() {
                this.$eventer.$emit('save-page');
            },
            'page.footer_color': _.debounce( function() {
                this.$eventer.$emit('save-page');
            }, 1000),
        },

        mounted() {
        },

        methods: {
        },

    }
</script>
