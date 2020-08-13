import Vue from "vue";
Vue.config.productionTip = false; // turn off the console log that says we are using Vue :)

import lodash from "lodash";
Object.defineProperty(Vue.prototype, "$lodash", { value: lodash });

import axios from "axios";
Object.defineProperty(Vue.prototype, "$http", { value: axios });

import moment from 'moment-timezone';
Object.defineProperty(Vue.prototype, "$moment", { value: moment });

axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

Object.defineProperty(Vue.prototype, "$eventer", { value: new Vue() });

import store from './Store';

import YoutubePlayer from '@/Components/YoutubePlayer.vue'
Vue.component('youtube-player', YoutubePlayer);

import Expander from '@/Components/Expander.vue'
Vue.component('expander', Expander);

import Echo from "laravel-echo"
window.Pusher = require('pusher-js');
Object.defineProperty(Vue.prototype, "$echo", { value: new Echo({
        broadcaster: 'pusher',
        key: process.env.MIX_PUSHER_APP_KEY,
        wsHost: process.env.MIX_WEBSOCKET_HOST,
        wsPort: process.env.MIX_WEBSOCKET_PORT,
        disableStats: true,
        encrypted: true,
    })
});

import VCalendar from 'v-calendar';
Vue.use(VCalendar, {
    datePicker: {
        popover: {
            visibility: 'focus',
        }
    },
    firstDayOfWeek: 1,
    formats: {
        title: 'MMMM YYYY',
        weekdays: 'W',
        navMonths: 'MMM',
        input: ['L', 'YYYY-MM-DD', 'YYYY/MM/DD'],
        dayPopover: 'WWW, MMM D, YYYY',
        data: ['L', 'YYYY-MM-DD', 'YYYY/MM/DD'],
    },
});

const app = new Vue({
    el: "#app",
    store,

    components: {
        'clock': () => import(/* webpackChunkName: "clock" */ '@/Components/Clock.vue'),
        'content-elements-editor': () => import(/* webpackChunkName: "content-elements-editor" */ '@/Components/ContentElementsEditor'),
        'page-editor': () => import(/* webpackChunkName: "page-editor" */ '@/Components/PageEditor'),
        'footer-editor': () => import(/* webpackChunkName: "footer-editor" */ '@/Components/FooterEditor'),
        'editing-button': () => import(/* webpackChunkName: "editing-button" */ '@/Components/EditingButton'),
        'feedback': () => import(/* webpackChunkName: "feedback" */ '@/Components/Feedback'),
        'page-tree': () => import(/* webpackChunkName: "page-tree" */ '@/Components/PageTree'),
        'photo-viewer': () => import(/* webpackChunkName: "photo-viewer" */ '@/Components/PhotoViewer'),
        'processing': () => import(/* webpackChunkName: "processing" */ '@/Components/Processing'),
        'saving-indicator': () => import(/* webpackChunkName: "saving-indicator" */ '@/Components/SavingIndicator'),
        'user-management': () => import(/* webpackChunkName: "user-management" */ '@/Components/UserManagement'),
        'page-access': () => import(/* webpackChunkName: "page-access" */ '@/Components/PageAccess'),
        'role-management': () => import(/* webpackChunkName: "role-management" */ '@/Components/RoleManagement'),
        'user-menu': () => import(/* webpackChunkName: "user-menu" */ '@/Components/UserMenu.vue'),
    },

    mounted() {

        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

        window.onYouTubeIframeAPIReady = function() {
            app.$store.dispatch('setYoutubeReady');
        }

        this.$store.dispatch('setWsState', this.$echo.connector.pusher.connection.state);
        this.$echo.connector.pusher.connection.bind('state_change', states => {
            this.$store.dispatch('setWsState', states.current);
        });

        this.$once('hook:destroyed', () => {
            this.$echo.leave('public');
        });
    },

    computed: {
        wsState() {
            return this.$store.state.wsState;
        }
    },

    watch: {
        wsState() {
            this.joinEchos();
        }
    },

    methods: {
        joinEchos: function() {
            this.$echo.channel('public');
        },
    }
});
