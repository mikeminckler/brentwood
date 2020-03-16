<template>

    <div class="m-4 p-4 bg-gray-200 ">
        <div class="mb-4">
            <input type="text" v-model="content.header" @change="saveContent()" placeholder="Header" />
        </div>

        <editor v-model="content.body" @input="saveContent()"></editor>
    </div>

</template>

<script>

    import Editor from '@/Components/Editor.vue';

    export default {

        props: ['content'],

        components: {
            'editor': Editor,
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
            saveContent: _.debounce( function() {

                console.log('SAVE');
                
            }, 1000),
        },

    }
</script>
