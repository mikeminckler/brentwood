<template>

    <div class="form px-4">

        <div class="flex items-center">
            <h2>{{ user.name }}</h2>
            <div class="bg-white rounded-full border overflow-hidden h-12 w-12 ml-4" v-if="user.avatar">
                <img :src="user.avatar" />
            </div>
        </div>

        <div class="">
            <div class="">{{ user.email }}</div>
        </div>

        <h3 class="mt-4">Roles</h3>
        <div class="mt-2">
            <autocomplete
                url="/roles/search"
                v-model="user.roles"
                name="roles"
                :multiple="true"
                dusk="user-roles"
                @remove="removeRole($event)"
                placeholder="Add Role"
                :hideLabel="true"
            ></autocomplete>
        </div>

    </div>


</template>

<script>

    import Feedback from '@/Mixins/Feedback';

    export default {

        props: ['user'],
        mixins: [Feedback],

        components: {
            'autocomplete': () => import(/* webpackChunkName: "autocomplete" */ '@/Components/Autocomplete'),
        },

        data() {
            return {
                saveUser: _.debounce( function() {
                    this.persist();
                }, 500),
            }
        },

        computed: {
        },

        watch: {
            user: {
                handler: function(oldValue, newValue) {
                    this.saveUser();
                },
                deep: true
            },
        },

        mounted() {
        },

        methods: {
            persist: function() {
                this.$http.post('/users/' + this.user.id, this.user).then( response => {
                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                });
            },

            removeRole: function(role) {

                let index = this.$lodash.findIndex(this.user.roles, r => {
                    return r.id === role.id;
                });
                this.user.roles.splice(index, 1);

            }
        },

    }
</script>
