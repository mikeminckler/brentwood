<template>

    <div class="px-8" v-if="objectable">

        <h2>{{ objectable.name }}</h2>

        <div class="form">

            <h3 class="mt-4">Add Permission</h3>
            <div class="mt-2">
                <autocomplete
                    url="/roles/search"
                    v-model="addRole"
                    name="role"
                    :multiple="false"
                    dusk="permissions-add-role"
                    placeholder="Add Role"
                    :hideLabel="true"
                ></autocomplete>
            </div>

            <div class="mt-2">
                <autocomplete
                    url="/users/search"
                    v-model="addUser"
                    name="user"
                    :multiple="false"
                    dusk="permissions-add-user"
                    placeholder="Add User"
                    :hideLabel="true"
                ></autocomplete>
            </div>

        </div>
        
        <div class="" v-if="objectable.permissions">
            <div class="mt-4" v-if="!objectable.permissions.length">No Permission</div>

            <div class="" v-if="objectable.permissions.length">
                <h3>Acccess</h3>
                <div class="mt-4">
                    <div v-for="(permission, index) in objectable.permissions" 
                        :key="permission.id" 
                        class="flex items-center pl-2 pr-1 py-1 bg-gray-100 m-1 rounded border"
                    >
                        <div class="flex-1">{{ permission.accessable_type.substring(11) }}: {{ permission.accessable.name }}</div>
                        <remove :dusk="'remove-permissions-' + permission.id" @remove="removePermission(permission, index)"></remove>
                    </div>
                </div>
            </div>
        </div>


    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';

    export default {

        props: ['item'],

        mixins: [Feedback],

        data() {
            return {
                objectable: null,
                addRole: null,
                addUser: null,
            }
        },

        components: {
            'autocomplete': () => import(/* webpackChunkName: "autocomplete" */ '@/Components/Autocomplete'),
            'remove': () => import(/* webpackChunkName: "remove" */ '@/Components/Remove.vue'),
        },

        computed: {
        },

        watch: {
            item() {
                this.loadPermissions();
            },
            addRole() {
                if (this.addRole) {
                    this.createPermission();
                }
            },
            addUser() {
                if (this.addUser) {
                    this.createPermission();
                }
            }
        },

        mounted() {
        },

        methods: {

            loadPermissions: function() {
                this.$http.post('/permissions/load', {objectable_id: this.item.id, objectable_type: this.item.type}).then( response => {
                    this.objectable = response.data.objectable;
                }, error => {
                    this.processErrors(error.response);
                });
            },

            createPermission: function() {

                let input = {
                    objectable_id: this.objectable.id,
                    objectable_type: this.objectable.type,
                    users: this.addUser ? [this.addUser] : null,
                    roles: this.addRole ? [this.addRole] : null,
                };

                this.$http.post('/permissions/create', input).then( response => {
                    this.processSuccess(response);
                    this.objectable = response.data.objectable;
                    this.addUser = null;
                    this.addRole = null;
                }, error => {
                    this.processErrors(error.response);
                });

            },

            removePermission: function(permission, index) {
                this.$http.post('/permissions/' + permission.id +'/destroy').then( response => {
                    this.processSuccess(response);
                    this.objectable.permissions.splice(index, 1);
                }, error => {
                    this.processErrors(error.response);
                });
            },

        },

    }
</script>
