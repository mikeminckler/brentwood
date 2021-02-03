<template>

    <div class="flex flex-col h-full border-r border-gray-300">

        <div class="bg-gray-300 text-gray-800 text-center">Chat</div>
        <div class="flex-1">
            <div class="odd:bg-gray-200 px-2"
                 v-for="message in messages"
            >
                <div class="text-sm text-gray-500">{{ message.name }}</div>
                <div class="">{{ message.message }}</div>
            </div>
        </div>

        <div class="">
            <textarea v-model="message" class="w-full p-1 leading-none" @keydown.enter.prevent="sendMessage()"></textarea>
        </div>
    </div>

</template>

<script>
    export default {

        props: ['room', 'name'],

        data() {
            return {
                messages: [],
                message: '',
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {
            this.joinRoom();
        },

        methods: {

            joinRoom: function() {
                this.$echo.join(this.room)

                    .here( (users) => {
                        console.log(users);
                    })
                    .joining( (user) => {
                        console.log(user.name);
                    })
                    .leaving( (user) => {
                        console.log(user.name);
                    })
                    .listen('NewMessage', (data) => {
                        console.log(data);
                    });
            },

            sendMessage: function() {

                this.messages.push({
                    name: this.$store.state.user.name,
                    message: this.message,
                });

                let input = {
                    message: this.message,
                };

                this.$http.post('/chat', input).then( response => {
                    this.message = '';
                    //this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                });

            }

        },

    }
</script>
