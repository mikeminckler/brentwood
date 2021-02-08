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
            <div class="px-4 py-2">
                <div class="flex items-baseline border-b border-gray-300">
                    <div class="text-lg px-2">{{ livestream.inquiry_users.length }} Registered</div>
                    <div class="link ml-4" @click="selectAllUsers()">{{ livestream.inquiry_users.length === selectedUsers.length ? 'Deselect' : 'Select' }} All</div>
                </div>

                <div class="mt-2 grid grid-cols-2">
                    <div class="">Name</div>
                    <div class="">Reminder Sent At</div>
                    <div class="ignore" :key="user.id" v-for="user in livestream.inquiry_users">
                        <checkbox-input class="py-1" v-model="user.id" :multiple="selectedUsers" :label="user.name"></checkbox-input>
                        <div class="py-1">{{ user.pivot.reminder_email_sent_at }}</div>
                    </div>
                </div>

                <div class="flex my-2">
                    <transition name="button-down">
                        <div class="flex button" @click="sendReminderEmails()" v-if="selectedUsers.length">
                            <div class="icon"><i class="fas fa-envelope"></i></div>
                            <div class="pl-2">Send {{ selectedUsers.length }} Reminder Emails</div>
                        </div>
                    </transition>
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
            'checkbox-input': () => import(/* webpackChunkName: "checkbox-input" */ '@/Components/CheckboxInput.vue'),
        },

        data() {
            return {
                selectedUsers: [],
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
                this.$http.post('/livestreams/' + this.livestream.id + '/send-reminder-emails', {user_ids: this.selectAllUsers}).then( response => {
                    this.processSuccess(response);
                    this.selectAllUsers = [];
                }, error => {
                    this.processErrors(error.response);
                });
            },

            selectAllUsers: function() {

                if (this.selectedUsers.length === this.livestream.inquiry_users.length) {
                    this.selectedUsers = [];
                } else {
                    this.selectedUsers = this.$lodash.map(this.livestream.inquiry_users, user => {
                        return user.id;
                    });
                }
            }
        },

    }
</script>
