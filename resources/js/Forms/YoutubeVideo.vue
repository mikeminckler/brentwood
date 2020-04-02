<template>

    <div class="relative">
        <div class="flex relative">

            <div class="flex-1 relative" v-if="!content.full_width"></div>
            <div class="relative flex-2 flex justify-center items-center relative z-2">
                <youtube-player
                    :video-id="content.video_id" 
                    :photo="photo" 
                    :uuid="uuid"
                    :title="content.title"
                    :full-width="content.full_width"
                    :remove="true"
                    @remove="removePhoto(photo, 0)"
                ></youtube-player>
            </div>

        </div>

        <div class="flex items-center justify-end relative bg-gray-200 p-2 shadow mt-4 z-2">

            <div class="form pl-2">
                <input type="text" v-model="videoId" placeholder="YouTube Video ID" />
            </div>

                <checkbox-input v-model="content.full_width" label="Full Width"></checkbox-input> 

            <div class="flex items-center px-2" v-if="!photo && videoId">
                <div class="button" @click="$eventer.$emit('add-files', fileUploadName)">
                    <div class="">Upload Banner Image</div>
                </div>
            </div>

            <div>
                <file-uploads
                    :name="fileUploadName"
                    v-model="uploads"
                    :multiple="multiplePhotos"
                    :items="photos"
                    type="image"
                ></file-uploads>
            </div>

            <div class="form pl-2" v-if="photo">
                <input type="text" v-model="content.title" placeholder="Title" />
            </div>

        </div>
    </div>


</template>

<script>

    import Feedback from '@/Mixins/Feedback';
    import FileUploads from '@/Components/FileUploads';
    import CheckboxInput from '@/Components/CheckboxInput';
    import Photos from '@/Mixins/Photos';

    export default {

        props: ['content', 'uuid'],
        mixins: [Feedback, Photos],

        components: {
            'file-uploads': FileUploads,
            'checkbox-input': CheckboxInput,
        },

        data() {
            return {
                videoId: '',
                multiplePhotos: false,
                setVideoId: _.debounce( function() {
                    this.content.video_id = this.videoId;
                }, 1000),
            }
        },

        computed: {
            contentVideoId() {
                return this.content.video_id;
            }
        },

        watch: {
            contentVideoId() {
                this.videoId = this.contentVideoId;
            },

            videoId() {
                if (this.contentVideoId !== this.videoId) {
                    this.setVideoId();
                }
            }
        },

        mounted() {
            if (this.content.video_id) {
                this.videoId = this.content.video_id;
            }
        },
    }
</script>
