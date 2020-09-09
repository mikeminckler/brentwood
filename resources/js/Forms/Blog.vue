<template>

    <div class="">

        <h1>Edit Blog</h1>

        <page-editor 
            :show-close="true" 
            resource="blogs"
            @close="$store.dispatch('resetPage')"
        ></page-editor>

        <content-elements-editor></content-elements-editor>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback.js';

    export default {

        props: ['blog'],

        mixins: [Feedback],

        components: {
            'content-elements-editor': () => import(/* webpackChunkName: "content-elements-editor" */ '@/Components/ContentElementsEditor.vue'),
            'page-editor': () => import(/* webpackChunkName: "page-editor" */ '@/Components/PageEditor.vue'),
            'remove': () => import(/* webpackChunkName: "remove" */ '@/Components/Remove.vue'),
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

            saveBlog: _.debounce(function() {

                let input = {
                    name: this.blog.name,
                    unlisted: this.blog.unlisted ? true : false,
                    sort_order: this.blog.sort_order,
                    content_elements: this.blog.content_elements,
                    publish_at: this.blog.publish_at,
                };

                this.$store.dispatch('startSaving', 'blog');
                this.$store.dispatch('setPageLoading', true);

                this.$http.post('/blogs/' + this.blog.id, input).then( response => {

                    this.processSuccess(response);
                    this.$store.dispatch('completeSaving', 'blog');

                    this.$nextTick(() => {
                        this.$store.dispatch('setPageLoading', false);
                    });
                }, error => {
                    this.processErrors(error.response);
                    this.$store.dispatch('completeSaving', 'blog');
                });

            }, 750),
        },

    }
</script>
