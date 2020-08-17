<template>

    <div class="">

    </div>

</template>

<script>
    export default {

        props: [],
        data() {
            return {
                userChannel: {},
                roleChannels: [],
            }
        },

        mounted() {
            this.$once('hook:destroyed', () => {
                this.$echo.leave('public');
            });

            this.joinUserChannel();
            this.joinRoleChannels();
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
        }

    }
</script>
