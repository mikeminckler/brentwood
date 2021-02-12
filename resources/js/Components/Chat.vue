<template>

    <div class="relative transition-all duration-500" :class="showChat ? 'flex-1 pb-100p md:pb-0' : 'flex-0'">
        <div class="absolute z-5 right-0">
            <div v-if="!showChat && !hideClose" class="flex absolute button-secondary right-0" @click="showChat = true">
                <div class=""><i class="fas fa-comment"></i></div>
                <div class="whitespace-nowrap pl-2">Show Chat</div>
            </div>
            <remove class="mr-2 md:mr-0" v-if="showChat && !hideClose" :transparent="true" @remove="showChat = false"></remove>
        </div>

        <transition name="chat">
            <div class="flex flex-col h-full bg-gray-100 shadow absolute w-full overflow-hidden" v-if="showChat">

                <div class="bg-white text-gray-800 text-center flex items-center justify-center">
                    <div class="">Chat</div>
                    <div class="ml-4 text-gray-500 flex items-center" :class="moderator ? 'cursor-pointer hover:bg-gray-100 hover:shadow' : ''" v-if="members.length > 0" @click="toggleShowMembers()">
                        <div class="text-sm"><i class="fas fa-user"></i></div>
                        <div class="pl-1">{{ members.length }}</div>
                    </div>
                </div>

                <transition name="chat-members">
                    <div class="text-sm bg-white shadow relative z-5 overflow-y-scroll px-2 py-1" v-if="moderator && showMembers" style="max-height: 50%;">
                        <div class="odd:bg-gray-100 cursor-pointer hover:text-primary" v-for="member in members" @click="setWhisperFromUser(member)">{{ member.name }} <span v-if="member.id === user.id" class="font-italic text-gray-800">You</span></div>
                    </div>
                </transition>

                <div class="flex-1 flex items-center justify-center" v-if="!user.id">
                    <p>Please <a href="#" class="link" @click.prevent="login">Login</a> to join Chat</p>
                </div>

                <div class="flex-1 flex flex-col" v-if="user.id">
                    <div class="flex-1 w-full relative">
                        <div class="w-full h-full absolute flex flex-col-reverse overflow-y-scroll">
                            <div class="px-2"
                                 :class="chat.id < 1 ? 'bg-yellow-100 bg-opacity-50 text-sm' : ( chat.whisper_ids ? 'bg-blue-100 odd:bg-blue-200' : 'bg-white odd:bg-gray-100') "
                                 :key="'chat-' + chat.id"
                                 v-for="chat in chats"
                            >
                                <div class="absolute right-0 flex text-sm mt-1" v-if="moderator && chat.id >= 1">
                                    <div class="px-1 cursor-pointer text-gray-400 hover:text-primary" @click="deleteMessage(chat)"><i class="fas fa-trash"></i></div>
                                </div>
                                <span class="text-sm" @click="banUser(chat)" v-if="moderator && chat.id >= 1"><i class="fas fa-ban"></i></span>
                                <span class="text-sm text-gray-500" :class="moderator || chat.whisper_ids ? 'cursor-pointer hover:text-primary' : ''" @click="setWhisper(chat)">{{ chat.name }}:</span>
                                <span v-if="chat.whisper_ids" class="italic text-gray-800 text-sm">private</span>
                                <span class="" :class="chat.deleted ? 'line-through text-gray-400' : ''">{{ chat.message }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-2 py-1 border-t shadow relative z-5" v-if="user.banned_at">You have been banned from Chat</div>

                    <div class="px-2 py-1 border-t shadow relative z-5" v-if="!user.banned_at">
                        <transition name="whisper-text">
                            <div class="text-sm pl-2 flex overflow-hidden" v-if="whisper">
                                <div class="">Message <span class="font-bold">{{ whisper.name }}</span></div>
                                <div class="pl-2 cursor-pointer" @click="whisper = null"><i class="fas fa-times"></i></div>
                            </div>
                        </transition>
                        <textarea v-model="message" 
                                  ref="message"
                                  class="w-full p-2 leading-none outline-none focus:border-gray-300 border rounded text-sm text-gray-600" 
                                  @keydown.enter.prevent="sendMessage()" 
                                  :placeholder="whisper ? 'Send a whisper to ' + whisper.name + '...' : 'Send a message to all users...'"
                        ></textarea>
                    </div>
                </div>

            </div>
        </transition>
    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';

    export default {

        props: ['room', 'hide-close', 'moderators'],

        mixins: [Feedback],

        components: {
            'remove': () => import(/* webpackChunkName: "remove" */ '@/Components/Remove'),
        },

        data() {
            return {
                showChat: true,
                showMembers: false,
                chats: [],
                message: '',
                members: [],
                whisper: null,
            }
        },

        computed: {
            user() {
                return this.$store.state.user;
            },
            pathname() {
                return window.location.pathname;
            },
            moderator() {
                if (this.$store.getters.hasRole('admin')) {
                    return true;
                }

                if (this.$lodash.find(this.moderators, moderator => {
                    return moderator.id === this.user.id;
                })) {
                    return true;
                }

                return false;
            }
        },

        watch: {
            user() {
                if (this.user.id) {
                    this.joinRooms();
                }
            }
        },

        mounted() {
            if (this.user.id) {
                this.joinRooms();
            }
        },

        methods: {

            joinRooms: function() {

                // for ban alerts
                this.$echo.channel('chat')
                    .listen('UserBanned', (data) => {
                        this.processBan(data.user);
                    });

                // for direct messages
                this.$echo.private('user.' + this.user.id)
                    .listen('WhisperCreated', (data) => {
                        this.chats.unshift(data.chat);
                    });

                // main events from chat
                this.$echo.join(this.room)

                    .here( (users) => {
                        this.members = users;
                        this.loadChat();
                    })

                    .joining( (user) => {

                        this.members.push(user);

                        if (this.moderator) {
                            let chat = {
                                id: '0.' + this.chats.length,
                                message: user.name + ' Joined',
                                user_id: user.id,
                                name: 'System',
                                deleted: false,
                                room: this.room,
                            };
                            this.chats.unshift(chat);
                        }
                    })

                    .leaving( (user) => {

                        this.members = this.$lodash.xor(this.members, [user]);

                        // post a system message in chat
                        if (this.moderator) {
                            let chat = {
                                id: '0.' + this.chats.length,
                                message: user.name + ' Left',
                                user_id: user.id,
                                name: 'System',
                                deleted: false,
                                room: this.room,
                            };
                            this.chats.unshift(chat);
                        }
                    })

                    .listen('ChatMessageCreated', (data) => {
                        this.chats.unshift(data.chat);
                    })

                    .listenForWhisper('purge-message', (chatId) => {
                        this.purgeMessage(chatId);
                    });
            },

            loadChat: function() {

                this.$http.post('chat/load', {room: this.room}).then( response => {
                    this.chats = response.data.chats;
                }, error => {
                    this.processErrors(error.response);
                });

            },

            sendMessage: function() {

                if (this.message.length) {
                    let input = {
                        room: this.room,
                        message: this.message,
                        //user_id: this.user.id,
                        whisper_id: this.whisper ? this.whisper.user_id : '',
                    };

                    this.$http.post('/chat/send-message', input).then( response => {
                        this.message = '';
                        this.chats.unshift(response.data.chat);
                        //this.processSuccess(response);
                    }, error => {
                        this.processErrors(error.response);
                    });
                }

            },

            processBan: function(user) {
                let chats = this.$lodash.filter(this.chats, c => {
                    return c.user_id === user.id;
                });

                this.$lodash.each(chats, c => {
                    c.message = 'Message Deleted';
                    c.deleted = true;
                });

                if (this.user.id === user.id) {
                    this.$store.dispatch('banUser');
                }
            },

            purgeMessage: function(chat) {
                let c = this.$lodash.find(this.chats, c => {
                    return c.id === chat.id;
                });

                if (c) {
                    c.message = 'Message Deleted';
                    c.deleted = true;
                }
            },

            deleteMessage: function(chat) {
                chat.deleted = true;
                this.$echo.join(this.room)
                    .whisper('purge-message', chat);

                this.$http.post('/chat/' + chat.id + '/delete').then( response => {
                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                });

            },

            banUser: function(chat) {

                if (this.moderator) {
                    var answer = confirm('Are you sure you want to BAN ' + chat.name + '?');
                    if (answer == true) {

                        this.$http.post('/users/' + chat.user_id + '/ban', {room: this.room}).then( response => {
                            this.processSuccess(response);
                        }, error => {
                            this.processErrors(error.response);
                        });
                    }
                }
            },

            login: function() {
                this.$http.post('/intended-url', {url: window.location.pathname}).then( response => {
                    window.location.href = '/login';
                }, error => {
                    this.processErrors(error.response);
                });
            },

            toggleShowMembers: function() {
                if (this.moderator) {
                    this.showMembers = !this.showMembers;
                }
            },

            setWhisper: function(chat) {
                if (this.moderator || chat.whisper_ids) {
                    this.whisper = {
                        user_id: chat.user_id,
                        name: chat.name,
                    };
                    this.$refs.message.focus();
                }
            },

            setWhisperFromUser: function(user) {
                if (this.moderator) {
                    this.whisper = {
                        user_id: user.id,
                        name: user.name,
                    };
                    this.showMembers = false;
                    this.$refs.message.focus();
                }
            },
            
        },

    }
</script>

<style>

@keyframes chat {
    0% {
        opacity: 0;
    }
    100%   {
        opacity: 1;
    }
}

.chat-enter-active {
    animation: chat var(--transition-time) ease-out;
}

.chat-leave-active {
    animation: chat var(--transition-time) reverse;
}

@keyframes chat-members {
    0% {
        opacity: 0;
        max-height: 0;
    }
    100%   {
        opacity: 1;
        max-height: 50%;
    }
}

.chat-members-enter-active {
    animation: chat-members var(--transition-time) ease-out;
}

.chat-members-leave-active {
    animation: chat-members var(--transition-time) reverse;
}

@keyframes whisper-text {
    0% {
        opacity: 0;
        max-height: 0;
    }
    100%   {
        opacity: 1;
        max-height: 20px;
    }
}

.whisper-text-enter-active {
    animation: whisper-text var(--transition-time) ease-out;
}

.whisper-text-leave-active {
    animation: whisper-text var(--transition-time) reverse;
}

</style>
