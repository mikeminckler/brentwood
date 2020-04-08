<template>

    <div class="relative w-full overflow-hidden z-4" :class="videoPadding" v-show="videoId" style="transition: padding calc(var(--transition-time) * 5)">

        <div class="absolute bottom-0 z-4 w-full h-full" v-if="$store.state.editing && photo">
            <div class="absolute right-0 bottom-0 transform rotate-90 origin-top-right w-32 mb-16" @click.stop>
                <div class="flex items-center px-2 py-1">
                    <input type="range" v-model="photo.offsetY" min="0" max="100" />
                </div>
            </div>

            <div class="absolute right-0 bottom-0 w-32 mb-16" @click.stop>
                <div class="flex items-center px-2 py-1">
                    <input type="range" v-model="photo.offsetX" min="0" max="100" />
                </div>
            </div>

        </div>

        <transition name="fade">
            <div class="photo z-3 fill" v-if="photo && !hideBanner">

                <div class="absolute z-3 w-full h-full flex items-center justify-center cursor-pointer border-b-4 border-wash"
                     @click="$eventer.$emit('play-video', uuid)"
                    >
                    <div class="flex absolute bottom-0 w-full items-center leading-none justify-center font-oswald text-xl md:text-2xl text-gray-700">
                        <div class="flex items-center px-4 py-2 bg-wash font-thin mb-4" 
                            v-if="title && !$store.state.editing"
                          >
                            {{ title }}
                        </div>
                    </div>
                    <div class="relative flex items-center justify-center text-6xl text-primary">
                        <div class="absolute bg-white w-8 h-6 z-2"></div>
                        <div class="relative z-3">
                            <i class="fab fa-youtube"></i>
                        </div>
                    </div>

                </div>
                <img :src="photo.large" :style="'object-position: ' + photo.offsetX + '% ' + photo.offsetY + '%;'">
            </div>
        </transition>

        <div :id="'player-' + uuid"></div>
    </div>

</template>

<script>
    export default {

        props: ['videoId', 'uuid', 'photo', 'title', 'fullWidth'],
        data() {
            return {
                player: {},
                hideBanner: false,
                videoPadding: 'pb-video',
                setPlayerState: _.debounce( function(state) {

                    if (state == YT.PlayerState.PLAYING || state == YT.PlayerState.BUFFERING) {
                        this.hideBanner = true;
                        this.videoPadding = 'pb-video';
                    }

                    if (state == YT.PlayerState.ENDED || state == YT.PlayerState.PAUSED) {
                        this.hideBanner = false;
                        this.setPadding();
                    }

                }, 500),
            }
        },

        computed: {
            ready() {
                return this.$store.state.youtubeReady;
            },
            hostname() {
                //return window.location.protocol + '//' + window.location.hostname;
                return window.location.hostname;
            },
            canPlay() {
                return this.$lodash.isFunction(this.player.playVideo);
            },
        },

        watch: {
            ready() {
                this.loadVideo();
            },
            videoId() {
                this.loadVideo();
            },
            fullWidth() {
                this.setPadding();
            }
        },

        mounted() {

            const listener = uuid => {
                this.playVideo(uuid);
            };
            this.$eventer.$on('play-video', listener);

            this.$once('hook:destroyed', () => {
                this.$eventer.$off('play-video', listener);
            });

            this.setPadding();

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
                                //origin: this.hostname,
                                host: this.hostname,
                                showinfo: 0,
                                modestbranding: 1,
                                iv_load_policy: 3,
                                enablejsapi: 1,
                                autoplay: 0,
                            },
                            events: {
                                'onStateChange': this.onPlayerStateChange
                            },
                        });

                    }
                }

            },

            onPlayerStateChange: function(event) {
                this.setPlayerState(event.data);
            },

            playVideo: function(uuid) {

                    if (this.uuid !== uuid) {

                        if (this.$lodash.isFunction(this.player.getPlayerState)) {
                            if (this.player.getPlayerState() === 1) {
                                this.player.pauseVideo();
                            }
                        }

                    } else {

                        if (this.canPlay) {
                            //this.hideBanner = true;
                            //this.videoPadding = 'pb-video';
                            this.player.playVideo();

                            let content = document.getElementById('c-' + this.uuid);
                            content.scrollIntoView();
                        }
                    }

            },

            setPadding: function() {
                if (this.fullWidth) {
                    this.videoPadding = 'pb-video md:pb-33p';
                } else {
                    this.videoPadding = 'pb-video';
                }
            },
        },

    }
</script>
