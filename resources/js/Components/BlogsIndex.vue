<template>

    <div class="">

        <h1>Blogs</h1>

        <div class="flex link mb-4" @click="createBlog">
            <div class="icon"><i class="fas fa-plus"></i></div>
            <div class="ml-2">Create Blog</div>
        </div>

        <div class="">
            <div class="" v-for="(blog, index) in blogs" v-if="!selectedBlog">
                <div class="" @click="selectedBlog = blog">{{ blog.name }}</div>
            </div>
        </div>

        <div class="" v-if="selectedBlog">
            <remove @remove="selectedBlog = null"></remove>
            <div class="form"><input type="text" v-model="blog.name" @enter="saveBlog" @focus="$event.target.select()" @change="saveBlog()" /></div>
        </div>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback.js';
    

    export default {

        props: [],
        mixins: [Feedback],

        components: {
            'remove': () => import(/* webpackChunkName: "remove" */ '@/Components/Remove'),
        },

        data() {
            return {
                blogs: [],
                selectedBlog: null,
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {
            this.loadBlogs();
        },

        methods: {

            loadBlogs: function() {
                this.$http.get('/blogs').then( response => {
                    this.blogs = response.data.data;
                }, error => {
                    this.processErrors(error.response);
                })
            },

            createBlog: function() {

                let input = {
                    name: 'Untitled Blog',
                    unlisted: false,
                    sort_order: 1,
                    content_elements: [],
                }

                this.$http.post('/blogs/create', input).then( response => {
                    this.processSuccess(response);
                    this.selectedBlog = response.data.page;
                }, error => {
                    this.processErrors(error.response);
                });
            },

            saveBlog: _.debounce(function() {

                let input = {
                    name: this.page.name,
                    unlisted: this.page.unlisted ? true : false,
                    sort_order: this.page.sort_order,
                    content_elements: this.page.content_elements,
                    publish_at: this.page.publish_at,
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
