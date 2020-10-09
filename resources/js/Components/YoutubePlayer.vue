<template>

    <div class="relative w-full overflow-hidden">
        <div class="relative w-full overflow-hidden z-4" :class="videoPadding" v-show="content.video_id" style="transition: padding calc(var(--transition-time) * 5)">

            <div class="absolute bottom-0 z-4 w-full h-full" v-if="$store.state.editing && banner">
                <div class="absolute right-0 bottom-0 transform rotate-90 origin-top-right w-32 mb-16" @click.stop>
                    <div class="flex items-center px-2 py-1">
                        <input type="range" v-model="banner.offsetY" min="0" max="100" />
                    </div>
                </div>

                <div class="absolute right-0 bottom-0 w-32 mb-16" @click.stop>
                    <div class="flex items-center px-2 py-1">
                        <input type="range" v-model="banner.offsetX" min="0" max="100" />
                    </div>
                </div>

            </div>

            <transition name="fade">
                <div class="photo z-3 fill" v-if="banner && !hideBanner">

                    <div class="absolute z-3 w-full h-full flex items-center justify-center cursor-pointer border-b-4 border-white border-opacity-75"
                         @click="$eventer.$emit('play-video', uuid)"
                        >
                        <div class="flex absolute bottom-0 w-full items-center leading-none justify-center font-oswald text-xl md:text-2xl text-gray-700">
                            <div class="flex items-center px-4 py-2 bg-white bg-opacity-75 font-thin mb-4" 
                                v-if="content.title && !$store.state.editing"
                              >
                                {{ content.title }}
                            </div>
                        </div>
                        <div class="relative flex items-center justify-center text-6xl text-primary">
                            <div class="absolute bg-white w-8 h-6 z-2"></div>
                            <div class="relative z-3">
                                <i class="fab fa-youtube"></i>
                            </div>
                        </div>

                    </div>
                    <img :src="banner.large" :style="'object-position: ' + banner.offsetX + '% ' + banner.offsetY + '%;'">
                </div>
            </transition>

            <div :id="'player-' + uuid"></div>
        </div>

        <div class="relative flex justify-center z-4" 
             :class="hideBanner ? 'mt-0' : 'md:-mt-16'" 
             style="transition: margin var(--transition-time) ease-out"
            v-if="content.full_width && content.body && !$store.state.editing">

            <slot></slot>

        </div>

    </div>

</template>

<script>
    export default {

        props: ['content', 'uuid', 'photo'],
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

                }, 100),
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
            banner() {
                return this.photo ? this.photo : this.content.photos[0];
            }
        },

        watch: {
            ready() {
                this.loadVideo();
            },
            'content.video_id': function (newVal, oldVal) {
                this.loadVideo();
            },
            'content.full_width': function(newVal, oldVal) {
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

                if (this.content.video_id) {

                    if (this.$lodash.isFunction(this.player.loadVideoById)) {
                        this.player.loadVideoById(this.content.video_id);
                    } else {

                        this.player = new YT.Player('player-' + this.uuid, {
                            videoId: this.content.video_id,
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
                            let elementRect = content.getBoundingClientRect();
                            let middle = content.offsetTop - (elementRect.height / 3);
                            window.scrollTo(0, middle);

                        }
                    }

            },

            setPadding: function() {
                if (this.content.full_width) {
                    if (this.content.body) {
                        this.videoPadding = 'pb-video md:pb-40p';
                    } else {
                        this.videoPadding = 'pb-video md:pb-33p';
                    }
                } else {
                    this.videoPadding = 'pb-video';
                }
            },
        },

    }
</script>
