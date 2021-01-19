<template>

    <div class="ignore" @click="$emit('selected', blog)">
        <div class="cursor-pointer py-1 px-2" :class="[blog.unlisted ? 'text-gray-500' : 'link']">{{ blog.name }}</div>

        <div class="">{{ blog.published_at ? displayDateTime(blog.published_at) : '' }}</div>

        <div class="px-2">
            <div class="" v-if="blog.publish_at && blog.can_be_published" ><span class="pr-2"><i class="fas fa-clock"></i></span>{{ displayDateTime(blog.publish_at) }}</div>
        </div>

        <div class="py-1 px-2 flex justify-end">

            <div class="text-primary px-2 cursor-pointer" v-if="blog.can_be_published" @click.stop="publish()"><i class="fas fa-sign-out-alt"></i></div>

            <div class="text-green-500 px-2" v-if="blog.published_version_id"><i class="fas fa-check"></i></div>

            <div class="px-2 cursor-pointer" v-if="blog.unlisted" @click.stop="reveal()"><i class="fas fa-eye"></i></div>
            <div class="px-2 cursor-pointer" v-if="!blog.unlisted" @click.stop="unlist()"><i class="fas fa-eye-slash"></i></div>

        </div>
    </div>

</template>

<script>

    import Dates from '@/Mixins/Dates.js';

    export default {

        mixins: [Dates],

        props: ['item'],

        ata() {
            return {
            
            }
        },

        computed: {
            blog() {
                return this.item;
            },
        },

        watch: {
        },

        mounted() {
        },

        methods: {

            publish: function() {

                var answer = confirm('Are you sure you want to PUBLISH this blog?');
                if (answer === true) {

                    this.$http.post('/blogs/' + this.blog.id + '/publish').then( response => {
                        location.reload();
                    }, error => {
                        this.processErrors(error.response);
                    });
                }

            },

            unlist: function() {

                this.$http.post('/blogs/' + this.blog.id + '/unlist').then( response => {
                    this.blog.unlisted = true;
                }, error => {
                    this.processErrors(error.response);
                });

            },

            reveal: function() {

                this.$http.post('/blogs/' + this.blog.id + '/reveal').then( response => {
                    this.blog.unlisted = false;
                }, error => {
                    this.processErrors(error.response);
                });

            },

        },

    }
</script>
