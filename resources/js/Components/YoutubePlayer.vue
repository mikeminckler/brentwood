<template>

    <div :id="'player-' + this.uuid"></div>

</template>

<script>
    export default {

        props: ['videoId', 'uuid'],
        data() {
            return {
                player: {},
            }
        },

        computed: {
            ready() {
                return this.$store.state.youtubeReady;
            },
            hostname() {
                return window.location.hostname;
            }
        },

        watch: {
            ready() {
                this.loadVideo();
            },
            videoId() {
                this.loadVideo();
            }
        },

        mounted() {
            if (this.ready) {
                this.loadVideo();
            }
        },

        methods: {
            loadVideo: function() {

                if (this.videoId) {

                    if (this.$lodash.isFunction(this.player.loadVideoById)) {
                        this.player.loadVideoById(this.videoId);
                    } else {

                        this.player = new YT.Player('player-' + this.uuid, {
                            videoId: this.videoId,
                            playerVars: {
                                origin: this.hostname,
                                showinfo: 0,
                                modestbranding: 1,
                                iv_load_policy: 3,
                                enablejsapi: 1,
                                autoplay: 0,
                            }
                        });

                    }
                }

            },
        },

    }
</script>
