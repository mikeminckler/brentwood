<template>

    <div class="flex relative">

        <div class="flex-1">
            <div class="px-4">
                <div class="" 
                    :class="isLocked('header') ? 'locked relative' : ''">
                    <input :class="[isLocked('header') ? 'locked' : '']" 
                        class="h2 outline-none"
                        @focus="whisperEditing('header')" 
                        @blur="whisperEditingComplete('header')"
                        type="text" 
                        v-model="content.header" 
                        placeholder="List Header" 
                        :disabled="isLocked('header')"
                    />


                </div>

                <form-tags v-model="content.tags"></form-tags>

                <div class="mt-4">
                    <div class="" v-for="blog in blogs"><div class="link" @click="selectedBlog = blog">{{ blog.name }}</div></div>
                </div>

                <div class="flex justify-center items-center w-full" v-if="paginator.prev_page_url || paginator.next_page_url">

                    <div class="flex-1 h-0 border-t border-gray-400 pr-2"></div>

                    <div class="px-2 mx-2 w-4 flex justify-center" :class="paginator.prev_page_url ? 'text-primary cursor-pointer' : 'text-gray-400'" @click="prevPage()">
                        <div><i class="fas fa-chevron-left"></i></div>
                    </div>

                    <div class="px-2 mx-2 w-4 flex justify-center" :class="paginator.next_page_url ? 'text-primary cursor-pointer' : 'text-gray-400'" @click="nextPage()">
                        <div><i class="fas fa-chevron-right"></i></div>
                    </div>

                    <div class="flex-1 h-0 border-t border-gray-400 pl-2"></div>

                </div>

            </div>
        </div>

        <div class="flex-2">
            <blog-preview v-if="selectedBlog" :blog="selectedBlog"></blog-preview>
        </div>

    </div>

</template>

<script>

    import Photos from '@/Mixins/Photos';
    import Feedback from '@/Mixins/Feedback';
    import SaveContent from '@/Mixins/SaveContent';
    import Whisper from '@/Mixins/Whisper';

    export default {

        props: [ 'content', 'uuid', 'first', 'contentElementIndex'],
        mixins: [ Feedback, SaveContent, Whisper ],

        components: {
            'form-tags': () => import(/* webpackChunkName: "form-tags" */ '@/Forms/Tags.vue'),
            'blog-preview': () => import(/* webpackChunkName: "blog-preview" */ '@/Models/BlogPreview.vue'),
        },

        data() {
            return {
                paginator: [],
                selectedBlog: null,
            }
        },

        computed: {
            blogs() {
                return this.$lodash.values(this.paginator.data);
            },
        },

        watch: {
            'content.tags': function() {
                this.loadBlogs();
            },
            blogs() {
                this.selectedBlog = this.blogs[0];
            }
        },

        mounted() {
            this.loadBlogs();
        },

        methods: {
            loadBlogs: function(url) {

                if (!url) {
                    url = '/blogs';
                }

                this.$http.post(url, {tags: this.content.tags}).then( response => {
                    this.paginator = response.data;
                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                }); 
            },

            prevPage: function() {
                if (this.paginator.prev_page_url) {
                    this.loadBlogs(this.paginator.prev_page_url);
                }
            },

            nextPage: function() {
                if (this.paginator.next_page_url) {
                    this.loadBlogs(this.paginator.next_page_url);
                }
            },
        },

    }
</script>
