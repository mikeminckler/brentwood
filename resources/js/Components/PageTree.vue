<template>

    <div v-if="editing"
        class="bg-gray-100 border-r border-gray-300 pt-2 overflow-y-scroll text-gray-700 pb-4"
        :style="maxHeight ? 'max-height: ' + maxHeight : ''"
    >
        <page-list :page="pageTree" 
            :key="pageTree.id" 
            :emit-event="emitEvent"
            :show-changes="showChanges"
            :show-content-elements="showContentElements"
            :expanded="expanded"
            @selected="$emit('selected', $event)"
            :sort="sort"
        ></page-list>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback'

    export default {

        props: ['emitEvent', 'showContentElements', 'expanded', 'showChanges', 'maxHeight', 'sort'],
        mixins: [Feedback],

        data() {
            return {
            }
        },

        components: {
            'page-list': () => import(/* webpackChunkName: "page-list" */ '@/Components/PageList.vue'),
        },

        computed: {
            editing() {
                return this.$store.state.editing;
            },
            pageTree() {
                return this.$store.state.pageTree;
            },
        },

        watch: {
        },

        mounted() {
        },

        methods: {
            goToPage: function(page) {
                window.location.href = page.full_slug;
            },
        },

    }
</script>

<style>

    @keyframes page-tree {
        0% {
            max-width: 0px;
            opacity: 0;
        }
        100%   {
            max-width: 500px;
            opacity: 1;
        }
    }

    .page-tree-enter-active {
        animation: page-tree var(--transition-time) ease-out;
    }

    .page-tree-leave-active {
        animation: page-tree var(--transition-time) reverse;
    }

</style>
