<template>

    <div class="bg-yellow-100 flex z-3" v-if="filteredUsers.length">
        <div class="icon px-1"><i class="fas fa-exclamation-circle"></i></div>
        <div class="" v-for="(user, index) in filteredUsers">
            {{ user.name }}{{ (index + 1) === filteredUsers.length ? '' : ',' }}&nbsp;
        </div>
        <div class="">{{ filteredUsers.length > 1 ? 'are' : 'is' }} also editing this page.</div>
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

            if (this.user.id) {
                this.joinUserChannel();
                this.joinRoleChannels();
                this.joinPageChannel();
            }
        },

        computed: {
            wsState() {
                return this.$store.state.wsState;
            },
            user() {
                return this.$store.state.user;
            },
            filteredUsers() {
                return this.$lodash.filter( this.users, user => {
                    return user.id !== this.user.id;
                });
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
                this.joinPageChannel();
            }
        },

        methods: {
            joinUserChannel: function() {
                this.userChannel = this.$echo.private('user.' + this.user.id);
            },

            joinRoleChannels: function() {
                this.$lodash.each(this.user.roles, role => {
                    this.roleChannels.push(this.$echo.private('role.' + role.id));
                });
            },

            joinPageChannel: function() {
                this.$echo.join('page.' + this.$store.state.page.id)
                    .here((users) => {
                        this.users = users;
                    })
                    .joining((user) => {
                        let index = this.$lodash.findIndex( this.users, u => {
                            return u.id === user.id;
                        });
                        if (index < 0) {
                            this.users.push(user);
                        }
                    })
                    .leaving((user) => {
                        let index = this.$lodash.findIndex( this.users, u => {
                            return u.id === user.id;
                        });
                        this.users.splice(index, 1);
                    });
            },

            leaveChannels: function() {
                this.$echo.leave('public');
                this.$echo.leave('user.' + this.user.id);
                this.$lodash.each(this.user.roles, role => {
                    this.$echo.leave('role.' + role.id);
                });
                this.$echo.leave('page.' + this.$store.state.page.id)
            },
        }

    }
</script>
