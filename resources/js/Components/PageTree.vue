<template>

    <page-list :page="pageTree" 
        :key="pageTree.id" 
        :emit-event="emitEvent"
        :show-changes="showChanges"
        :show-content-elements="showContentElements"
        :expanded="expanded"
        @selected="$emit('selected', $event)"
        :sort="sort"
        :insert="insert"
    ></page-list>

</template>

<script>

    import Feedback from '@/Mixins/Feedback'

    export default {

        props: ['emitEvent', 'showContentElements', 'expanded', 'showChanges', 'maxHeight', 'sort', 'insert'],
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
            editing() {
                if (this.editing) {
                    this.loadPageTree();
                }
            }
        },

        mounted() {

            /**
            * here we setup a listener to refresh the page tree
            * you can see the emitter in Components/ContentEditor
            * you can emit refresh-page-tree from any component and the page tree will listen it
            */
            const refreshPageTree = event => {
                this.loadPageTree();
            };
            this.$eventer.$on('refresh-page-tree', refreshPageTree);

            this.$once('hook:destroyed', () => {
                this.$eventer.$off('refresh-page-tree', refreshPageTree);
                this.$echo.leave('role.pages-editor');
            });

            this.loadPageTree();

            this.$echo.private('role.pages-editor')
                .listen('PageSaved', data => {
                    this.loadPageTree();
                });
        },

        methods: {
            goToPage: function(page) {
                window.location.href = page.full_slug;
            },

            loadPageTree: _.debounce( function() {

                this.$store.dispatch('setPageLoading', true);

                let url = '/pages';

                if (this.showContentElements) {
                    url = '/pages?preview=true';
                }

                this.$http.get(url).then( response => {
                    this.$store.dispatch('setPageTree', response.data.home_page);

                    this.$nextTick(() => {
                        this.$store.dispatch('setPageLoading', false);
                    });
                }, error => {
                    //console.log(error.response);
                });
            }, 100),
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
