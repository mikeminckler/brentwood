<template>

    <div v-if="editing"
        class="bg-gray-100 border-r border-gray-300 pt-8"
        style="width: 300px"
    >
        <page-list :page="homePage" :key="homePage.id"></page-list>
    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback'
    import PageList from '@/Components/PageList.vue'

    export default {

        props: [],
        mixins: [Feedback],

        data() {
            return {
                homePage: [],
            }
        },

        components: {
            'page-list': PageList,
        },

        computed: {
            editing() {
                return this.$store.state.editing;
            }
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
            const listener = event => {
                this.loadPageTree();
            };
            this.$eventer.$on('refresh-page-tree', listener);

            this.$once('hook:destroyed', () => {
                this.$eventer.$off('refresh-page-tree', listener);
            });

            if (this.editing) {
                this.loadPageTree();
            }
        },

        methods: {
            loadPageTree: function() {
                this.$http.get('/pages').then( response => {
                    this.homePage = response.data.home_page;
                }, error => {
                    this.processErrors(error.response);
                });
            }
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