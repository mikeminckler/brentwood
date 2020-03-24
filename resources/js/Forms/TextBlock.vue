<template>

    <div class="flex">

        <div class="flex-1">
        </div>

        <div class="flex-2">

            <div class="text-block">
                <div class="h2">
                    <input type="text" v-model="content.header" @change="saveContent()" placeholder="Header" />
                </div>

                <editor v-model="content.body" 
                        @input="saveContent()"
                        placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit."
                ></editor>
            </div>

        </div>

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

                this.$emit('save');
                
            }, 1000),

        },

    }
</script>
