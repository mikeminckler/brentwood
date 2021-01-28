<template>

    <div class="mt-8 mx-12">

        <h1>User Management</h1>


        <div class="flex my-4">

            <div class="flex-1">

                <div class="link" v-for="(user, index) in users"
                    @click="selectedUser = user"
                > {{ user.name }} </div>

            </div>

            <div class="flex-2">
                <form-user v-if="selectedUser" :user="selectedUser"></form-user>
            </div>

        </div>

    </div>

</template>

<script>

    export default {

        props: [],

        components: {
            'form-user': () => import(/* webpackChunkName: "form-user" */ '@/Forms/User'),
        },

        data() {
            return {
                users: [],
                selectedUser: null,
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {
            this.loadUsers();
        },

        methods: {

            loadUsers: function() {
                this.$http.get('/users/load').then( response => {
                    this.users = response.data.users;
                }, error => {
                    this.processErrors(error.response);
                })
            },


        },

    }
</script>
