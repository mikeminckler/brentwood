<template>

    <div class="mt-8">

        <div class="" v-if="$store.state.page.id < 1">

            <h1>Blogs</h1>

            <div class="grid grid-blogs mt-2 py-2">
                <div class="flex link" @click="createBlog()">
                    <div class="icon"><i class="fas fa-plus"></i></div>
                    <div class="ml-2">Create Blog</div>
                </div>
                <div class="font-bold">Published At</div>
                <div class="font-bold">Scheduled Launch</div>
                <div class=""></div>
            </div>

            <div class="">
                <paginator resource="blogs" @selected="editBlog($event)"></paginator>
            </div>
        </div>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback.js';

    export default {

        props: [],
        mixins: [Feedback],

        components: {
            'paginator': () => import(/* webpackChunkName: "paginator" */ '@/Components/Paginator.vue'),
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

            createBlog: function() {

                let input = {
                    name: 'Untitled Blog',
                    unlisted: false,
                    sort_order: 1,
                    content_elements: [],
                }

                this.$http.post('/blogs/create', input).then( response => {
                    this.processSuccess(response);
                    window.location.href = response.data.page.full_slug;
                }, error => {
                    this.processErrors(error.response);
                });
            },

            editBlog: function(blog) {
                window.location.href = blog.full_slug;
            }

        },

    }
</script>
