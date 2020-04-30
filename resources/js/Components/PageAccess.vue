<template>


    <div class="flex mt-8">
        <div class="flex-1">
            <h1>Page Access</h1>
            <page-tree 
                :emit-event="true" 
                @selected="selectedPageId = $event"
                :expanded="true"
            ></page-tree>
        </div>
        <div class="flex-2">

            <div class="px-8" v-if="page.id">

                <h2>{{ page.name }}</h2>
                <div class="italic">/{{ page.full_slug }}</div>

                <div class="form">

                    <h3 class="mt-4">Add Access</h3>
                    <div class="mt-2">
                        <autocomplete
                            url="/roles/search"
                            v-model="addRole"
                            name="role"
                            :multiple="false"
                            dusk="page-access-add-role"
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
                            dusk="page-access-add-user"
                            placeholder="Add User"
                            :hideLabel="true"
                        ></autocomplete>
                    </div>

                </div>
                

                <div class="mt-4" v-if="!page.page_accesses.length">No Page Acceses</div>

                <h3>Page Acccess</h3>
                <div class="mt-4">
                    <div v-for="(page_access, index) in page.page_accesses" 
                        :key="page_access.id" 
                        class="flex items-center pl-2 pr-1 py-1 bg-gray-100 m-1 rounded border"
                    >
                        <div class="flex-1">{{ page_access.accessable_type.substring(4) }}: {{ page_access.accessable.name }}</div>
                        <remove :dusk="'remove-page-access-' + page_access.id" @remove="removePageAccess(page_access, index)"></remove>
                    </div>
                </div>


            </div>

        </div>
    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';

    export default {

        props: [],
        mixins: [Feedback],

        components: {
            'page-tree': () => import(/* webpackChunkName: "page-tree" */ '@/Components/PageTree'),
            'remove': () => import(/* webpackChunkName: "remove" */ '@/Components/Remove'),
            'autocomplete': () => import(/* webpackChunkName: "autocomplete" */ '@/Components/Autocomplete'),
        },

        data() {
            return {
                selectedPageId: null,
                page: {},
                addRole: null,
                addUser: null,
            }
        },

        computed: {
        },

        watch: {
            selectedPageId() {
                this.loadPage();
            },
            addRole() {
                if (this.addRole) {
                    this.createPageAccess();
                }
            },
            addUser() {
                if (this.addUser) {
                    this.createPageAccess();
                }
            }
        },

        mounted() {
        },

        methods: {
            loadPage: function() {
                this.$http.get('/page-accesses/page/' + this.selectedPageId).then( response => {
                    this.page = response.data.page;
                }, error => {
                    this.processErrors(error.response);
                });
            },

            createPageAccess: function() {

                let input = {
                    page_id: this.page.id,
                    users: this.addUser ? [this.addUser] : null,
                    roles: this.addRole ? [this.addRole] : null,
                };

                this.$http.post('/page-accesses/create', input).then( response => {
                    this.processSuccess(response);
                    this.page = response.data.page;
                    this.addUser = null;
                    this.addRole = null;
                }, error => {
                    this.processErrors(error.response);
                });

            },

            removePageAccess: function(page_access, index) {
                this.$http.post('/page-accesses/' + page_access.id +'/destroy').then( response => {
                    this.processSuccess(response);
                    this.page.page_accesses.splice(index, 1);
                }, error => {
                    this.processErrors(error.response);
                });
            },

        },

    }
</script>
