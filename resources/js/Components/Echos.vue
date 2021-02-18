<template>

    <div class="h-0 overflow-visible" v-if="editing">
        <transition name="presence">
            <div class="bg-yellow-100 flex z-3 relative" v-if="filteredUsers.length">
                <div class="icon px-1"><i class="fas fa-exclamation-circle"></i></div>
                <div class="" v-for="(user, index) in filteredUsers">
                    {{ user.name }}{{ (index + 1) === filteredUsers.length ? '' : ',' }}&nbsp;
                </div>
                <div class="">{{ filteredUsers.length > 1 ? 'are' : 'is' }} also editing this page.</div>
            </div>
        </transition>
    </div>

</template>

<script>
    export default {

        props: ['editing'],
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
                /*
                this.$lodash.each(this.user.roles, role => {
                    let name = role.name;
                    this.roleChannels.push(this.$echo.private('role.' + name.replace(/\s/g, '.').toLowerCase()));
                });
                */
            },

            joinPageChannel: function() {
                if (this.$store.state.page.id > 0) {
                    this.$echo.join('page.' + this.$store.state.page.id)
                        .here((users) => {
                            this.users = users;
                        })
                        .joining((user) => {
                            let index = this.$lodash.findIndex( this.users, u => {
                                return u.id === user.id;
                            });
                            if (index < 0 && this.editing) {
                                this.users.push(user);
                            }
                        })
                        .leaving((user) => {
                            let index = this.$lodash.findIndex( this.users, u => {
                                return u.id === user.id;
                            });
                            this.users.splice(index, 1);
                        });
                }
            },

            leaveChannels: function() {
                this.$echo.leave('public');
                this.$echo.leave('user.' + this.user.id);
                this.$lodash.each(this.user.roles, role => {
                    this.$echo.leave('role.' + role.name);
                });
                if (this.$store.state.page.id > 0) {
                    this.$echo.leave('page.' + this.$store.state.page.id)
                }
            },
        }

    }
</script>

<style>


@keyframes presence {
    0% {
        opacity: 0;
        max-height: 0;
    }
    100%   {
        opacity: 1;
        max-height: 24px;
    }
}

.presence-enter-active {
    animation: presence var(--transition-time) ease-out;
    overflow: hidden;
}

.presence-leave-active {
    animation: presence var(--transition-time) reverse;
    overflow: hidden;
}

</style>
