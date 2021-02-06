<template>

    <div class="ignore">

        <div class="grid grid-livestream-row">
            <div class="grid-cell flex justify-center"> <div class="px-2 border border-gray-300 bg-white rounded cursor-pointer" @click="$eventer.$emit('toggle-expander', 'livestream-' + livestream.id)">{{ livestream.inquiry_users.length }}</div></div>
            <div class="grid-cell"><a @click.stop :href="'/livestreams/' + livestream.id" title="View Livestream"><div class="icon"><i class="fas fa-eye"></i></div></a></div>
            <div class="grid-cell cursor-pointer text-primary" @click="$emit('selected', livestream)">{{ livestream.name }}</div>
            <div class="grid-cell">
                <div class="bg-white border border-gray-300 px-1 rounded flex items-center" v-for="tag in livestream.tags">
                    <div class="icon text-sm text-gray-400"><i class="fas fa-tag"></i></div>
                    <div class="pl-1">{{ tag.name }}</div>
                </div>
            </div>
            <div class="grid-cell">{{ displayDateTime(livestream.start_date) }}</div>
            <div class="grid-cell">
                <a class="inline-flex items-center" :href="'https://studio.youtube.com/video/' + livestream.video_id + '/livestreaming'" target="_blank">
                    <div class="icon"><i class="fab fa-youtube"></i></div>
                    <div class="pl-2">{{ livestream.video_id }}</div>
                </a>
            </div>
            <div class="grid-cell">
                <div class="" v-if="!livestream.enable_chat">Chat Disabled</div>
                <div class="inline-flex items-center link" @click="openChat()" v-if="livestream.enable_chat">
                    <div class="icon"><i class="fas fa-comments"></i></div>
                    <div class="pl-2">Live Chat</div>
                </div>
            </div>
        </div>

        <expander :uuid="'livestream-' + livestream.id" :backend="true">
            <div class="px-4 py-2 flex">
                <div class="">{{ livestream.inquiry_users.length }} Registered</div>
                <div class="flex button ml-4" @click="sendReminderEmails()">
                    <div class="icon"><i class="fas fa-envelope"></i></div>
                    <div class="pl-2">Send Reminder Emails</div>
                </div>
            </div>
        </expander>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback.js';
    import Dates from '@/Mixins/Dates.js';

    export default {

        mixins: [Dates, Feedback],

        props: ['item'],

        components: {
            'expander': () => import(/* webpackChunkName: "expander" */ '@/Components/Expander.vue'),
        },

        data() {
            return {
            
            }
        },

        computed: {
            livestream() {
                return this.item;
            },
        },

        watch: {
        },

        mounted() {
        },

        methods: {
            openChat: function() {
                window.open('/chat/view/' + this.livestream.chat_room, this.livestream.chat_room, 'width=600,height=800,scrollbars=yes');
                //window.open('https://www.youtube.com/live_chat?v=' + this.livestream.video_id + '&embed_domain=brentwood.ca', 'livestream-' + this.livestream.video_id, 'width=600,height=800,scrollbars=yes');
            },

            sendReminderEmails: function() {
                this.$http.post('/livestreams/' + this.livestream.id + '/send-reminder-emails').then( response => {
                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                });
            },
        },

    }
</script>
