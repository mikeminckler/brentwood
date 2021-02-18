<template>

    <div class="w-full">

        <div class="flex-1 last:mr-0 mr-4 my-2 w-full"
            v-for="tag in tags"
            :key="'tag-' + tag.id"
        >
            <div class="w-full text-center border px-4 py-2 cursor-pointer whitespace-nowrap"
                 :class="$lodash.find(selectedTags, tag) ? 'bg-primary text-white font-bold' : 'bg-gray-200 hover:bg-white'"
                 @click.stop="$emit('selected', tag)"
                 v-if="!filterTags(tag.tags).length && ignoreCheck(tag)"
            >
                {{ tag.name }}
            </div>

            <div class="mt-4" v-if="filterTags(tag.tags).length && ignoreCheck(tag)">
                <div class="text-gray-700 py-1 pl-4 bg-gray-300">{{ tag.name }}</div>
                <tags-selector class="flex flex-wrap" :tags="filterTags(tag.tags)" :ignore-tags="ignoreTags" :selected-tags="selectedTags" @selected="$emit('selected', $event)"></tags-selector>
            </div>

        </div>

    </div>

</template>

<script>
    export default {

        props: ['tags', 'selectedTags', 'ignoreTags'],

        components: {
            'tags-selector': () => import(/* webpackChunkName: "tags-selector" */ '@/Components/TagsSelector.vue'),
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

            ignoreCheck: function(tag) {
                return this.$lodash.findIndex(this.ignoreTags, t => {
                    return t === tag.name;
                }) >= 0 ? false : true;
            },

            filterTags: function(tags) {
                return this.$lodash.filter(tags, tag => {
                    return this.ignoreCheck(tag);
                });
            }
        },

    }
</script>
