<template>

    <div class="">
        <div class="" v-for="user in users">
            {{ user.name }}
        </div>
    </div>

</template>

<script>
    export default {

        props: [],
        data() {
            return {
                users: [],
                userChannel: {},
                roleChannels: [],
            }
        },

        mounted() {
            this.$once('hook:destroyed', () => {
                this.leaveChannels();
            });

            this.joinUserChannel();
            this.joinRoleChannels();
            this.joinPageChannel();
        },

        computed: {
            wsState() {
                return this.$store.state.wsState;
            },
            user() {
                return this.$store.state.user;
            }
        },

        watch: {
            wsState() {
                if (this.wsState === 'connected') {
                    this.$echo.channel('public');
                }
            },
            user() {
                this.joinUserChannel();
                this.joinRoleChannels();
            }
        },

        methods: {
            joinUserChannel: function() {
                if (this.user.id) {
                    this.userChannel = this.$echo.private('user.' + this.user.id);
                }
            },

            joinRoleChannels: function() {
                if (this.user.id) {
                    this.$lodash.each(this.user.roles, role => {
                        this.roleChannels.push(this.$echo.private('role.' + role.id));
                    });
                }
            },

            joinPageChannel: function() {
                if (this.user.id) {
                    this.$echo.join('page.' + this.$store.state.page.id)
                    .here((users) => {
                        this.users = users;
                    })
                    .joining((user) => {
                        console.log(user.name);
                    })
                    .leaving((user) => {
                        console.log(user.name);
                    })
                    .listenForWhisper('editing', (e) => {
                        console.log(e);
                    });
                }
            },

            leaveChannels: function() {
                this.$echo.leave('public');
                this.$echo.leave('user.' + this.user.id);
                this.$lodash.each(this.user.roles, role => {
                    this.$echo.leave('role.' + role.id);
                });
            },
        }

    }
</script>
