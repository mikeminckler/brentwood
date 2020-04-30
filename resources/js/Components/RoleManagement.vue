<template>

    <div class="mt-8 flex">

        <div class="flex-1">
            <h1>Roles</h1>

            <div class="">
                <div class="flex link" @click="createRole">
                    <div class="icon"><i class="fas fa-plus"></i></div>
                    <div class="ml-2">Create Role</div>
                </div>
                <div class="link" @click="selectedRole = role" v-for="role in roles">{{ role.name }}</div>
            </div>
        </div>

        <div class="flex-2">
            <form-role v-if="selectedRole" :role.sync="selectedRole"></form-role>
        </div>

    </div>

</template>

<script>
    export default {

        props: ['roles'],

        components: {
            'form-role': () => import(/* webpackChunkName: "form-role" */ '@/Forms/Role'),
        },

        data() {
            return {
                selectedRole: null,
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {
        },

        methods: {
            createRole: function() {

                this.selectedRole = {
                    id: 0,
                    name: '',
                };

            }
        },

    }
</script>
