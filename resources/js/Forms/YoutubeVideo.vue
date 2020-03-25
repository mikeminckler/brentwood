<template>

    <div class="relative">
        <div class="flex relative">

            <div class="flex-1 relative"></div>
            <div class="flex-2 flex justify-center items-center relative">
                <youtube-player :video-id="content.video_id" :uuid="uuid"></youtube-player>
            </div>

        </div>

        <div class="flex relative bg-gray-200 p-2 shadow mt-4 z-2">

            <div class="flex-1 relative"></div>

            <div class="flex-2 flex relative">
                <div class="form pl-2">
                    <input type="text" v-model="videoId" placeholder="YouTube Video ID" />
                </div>
            </div>

        </div>
    </div>


</template>

<script>

    import Feedback from '@/Mixins/Feedback';

    export default {

        props: ['content', 'uuid'],
        mixins: [Feedback],

        data() {
            return {
                videoId: '',
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

        methods: {

        },

    }
</script>
