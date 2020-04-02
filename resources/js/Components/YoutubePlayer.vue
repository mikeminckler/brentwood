<template>

    <div class="relative w-full overflow-hidden" :class="videoPadding" v-show="videoId" style="transition: padding calc(var(--transition-time) * 5)">


        <div class="absolute bottom-0 z-4 w-full h-full" v-if="$store.state.editing">
            <div class="absolute right-0 bottom-0 transform rotate-90 origin-top-right w-32 mb-6" @click.stop>
                <div class="flex items-center px-2 py-1">
                    <input type="range" v-model="photo.offsetY" min="0" max="100" />
                </div>
            </div>

            <div class="absolute right-0 bottom-0 w-32 mb-6" @click.stop>
                <div class="flex items-center px-2 py-1">
                    <input type="range" v-model="photo.offsetX" min="0" max="100" />
                </div>
            </div>
        </div>

        <transition name="fade">
            <div class="photo z-3 fill" v-if="photo && !hideBanner">

                <div class="absolute z-3 w-full h-full flex items-center justify-center text-6xl text-primary hover:text-primaryHover cursor-pointer border-b-2 border-transparent hover:border-primary"
                     @click="playVideo()"
                    >
                    <div class="flex absolute bottom-0 mb-8 w-full items-center justify-center" v-if="title">
                        <div class="font-oswald h-12 flex items-center leading-none px-4 border-l-2 border-primary text-2xl text-gray-700 bg-white">
                            {{ title }}
                        </div>
                        <div class="p-2 bg-primary">
                            <img class="h-8" src="/images/icon_white.svg" />
                        </div>
                    </div>
                    <div class="relative flex items-center justify-center">
                        <div class="absolute bg-white w-8 h-6 z-1"></div>
                        <div class="relative z-2">
                            <i class="fab fa-youtube"></i>
                        </div>
                    </div>


                    <div v-if="remove" class="absolute remove-icon right-0 bottom-0" @click.stop="$emit('remove')"><i class="fas fa-times"></i></div>
                </div>
                <img :src="photo.large" :style="'object-position: ' + photo.offsetX + '% ' + photo.offsetY + '%;'">
            </div>
        </transition>

        <div :id="'player-' + this.uuid"></div>
    </div>

</template>

<script>
    export default {

        props: ['videoId', 'uuid', 'photo', 'remove', 'title', 'fullWidth'],
        data() {
            return {
                player: {},
                hideBanner: false,
                videoPadding: 'pb-video',
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
                if (event.data == YT.PlayerState.ENDED) {
                    this.hideBanner = false;
                    this.setPadding();
                }
            },

            playVideo: function(uuid) {

                let start = true;
                if (uuid) {
                    if (this.uuid !== uuid) {
                        start = false;
                    }
                }

                if (this.canPlay && start) {
                    this.hideBanner = true;
                    this.videoPadding = 'pb-video';
                    this.player.playVideo();
                }

            },

            setPadding: function() {
                if (this.fullWidth) {
                    this.videoPadding = 'pb-33p';
                } else {
                    this.videoPadding = 'pb-video';
                }
            },
        },

    }
</script>
