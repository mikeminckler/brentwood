<template>

    <div class="blog-preview h-0 overflow-visible">
        <slot v-if="!blog"></slot>
        <div v-if="html" v-html="html"></div>
    </div>

</template>

<script>
    export default {

        props: [],
        data() {
            return {
                blog: null,
                html: null,
            }
        },

        computed: {
        },

        watch: {
            blog() {
                this.loadBlog();
            }
        },

        mounted() {


            const listener = data => {
                this.blog = data;
            };
            this.$eventer.$on('blog-preview', listener);

            this.$once('hook:destroyed', () => {
                this.$eventer.$off('blog-preview', listener);
            });

        },

        methods: {
            loadBlog: function() {

                this.$http.get(this.blog.full_slug, {params: {'render': true}} ).then( response => {
                    this.html = response.data.html;
                }, error => {
                    this.processErrors(error.response);
                });
            }
        },

    }
</script>
