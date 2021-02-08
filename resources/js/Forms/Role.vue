<template>

    <div class="form px-4">

        <h2>Edit {{ role.name }}</h2>
        <div class="flex items-center">
            <div class="input">
                <input type="text" autofocus v-model="role.name" placeholder="Name" />
            </div>
        </div>

        <h3 class="mt-4">Users</h3>
        <div class="mt-2">
            <autocomplete
                url="/users/search"
                v-model="role.users"
                name="users"
                :multiple="true"
                dusk="role-users"
                @remove="removeUser($event)"
                placeholder="Add User"
                :hideLabel="true"
            ></autocomplete>
        </div>

        <div class="">
            <div class="button" @click="save()">
                <div class="icon"><i class="fas fa-save"></i></div>
                <div class="pl-2">Save Role</div>
            </div>
        </div>

    </div>


</template>

<script>

    import Feedback from '@/Mixins/Feedback';

    export default {

        props: ['role'],
        mixins: [Feedback],

        components: {
            'autocomplete': () => import(/* webpackChunkName: "autocomplete" */ '@/Components/Autocomplete'),
        },

        data() {
            return {
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {
        },

        methods: {
            save: function() {
                this.$http.post('/roles/' + this.role.id, this.role).then( response => {
                    this.processSuccess(response);
                    this.$emit('update:role', response.data.role);
                }, error => {
                    this.processErrors(error.response);
                });
            },

            removeUser: function(user) {

                let index = this.$lodash.findIndex(this.role.users, u => {
                    return u.id === user.id;
                });
                this.role.users.splice(index, 1);
                this.save();

            }
        },

    }
</script>
