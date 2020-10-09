<template>

    <div class="px-8 flex flex-col overflow-hidden h-full">

        <div class="flex-1 flex flex-col">
            <h1>{{ blog.name }}</h1>

            <div class="flex-1 overflow-hidden mt-4" v-if="textBlock">
                <div class="h-0 overflow-visible">
                    <span v-html="textBlock.content.body"></span>
                </div>
            </div>

            <div class="my-2 link">Read More...</div>
        </div>

    </div>

</template>

<script>
    export default {

        props: ['blog'],

        data() {
            return {
            
            }
        },

        computed: {
            textBlock() {
                return this.$lodash.find(this.blog.content_elements, ce => {
                    return ce.type === 'text-block';
                });
            },
        },

        watch: {
        },

        mounted() {
        },

        methods: {
        },

    }
</script>
