<template>

    <div class="w-full">

        <div class="flex-1 last:mr-0 mr-4 my-2 w-full"
            v-for="tag in $lodash.omitBy(tags, $lodash.isNull)"
            v-if="tag.name !== 'Admissions'"
            :key="'tag-' + tag.id"
        >
            <div class="w-full text-center border px-4 py-2 cursor-pointer whitespace-nowrap"
                 :class="$lodash.find(selectedTags, tag) ? 'bg-primary text-white font-bold' : 'bg-gray-200 hover:bg-white'"
                 @click.stop="$emit('selected', tag)"
                 v-if="!tag.tags"
            >{{ tag.name }}</div>

            <div class="" v-if="tag.tags">
                <div class="text-gray-700 py-1 pl-4 bg-gray-300 rounded-t">{{ tag.name }}</div>
                <tags-selector class="flex flex-wrap" :tags="tag.tags" :selected-tags="selectedTags" @selected="$emit('selected', $event)"></tags-selector>
            </div>

        </div>

    </div>

</template>

<script>
    export default {

        props: ['tags', 'selectedTags'],

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
        },

    }
</script>
