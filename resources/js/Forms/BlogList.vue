<template>

    <div class="flex relative">

        <div class="flex-1">
            <div class="p-4">
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
                    <div class="" v-for="blog in blogs"><a :href="blog.full_slug">{{ blog.name }}</a></div>
                </div>

            </div>
        </div>
        <div class="flex-2">BLOG PREVIEW</div>

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
        },

        data() {
            return {
                blogs: [],
            }
        },

        computed: {
        },

        watch: {
            'content.tags': function() {
                this.loadBlogs();
            },
        },

        mounted() {
            this.loadBlogs();
        },

        methods: {
            loadBlogs: function() {
                this.$http.post('/blogs', {tags: this.content.tags}).then( response => {
                    this.blogs = response.data.data;
                   this.processSuccess(response);
               }, error => {
                   this.processErrors(error.response);
               }); 
            }
        },

    }
</script>
