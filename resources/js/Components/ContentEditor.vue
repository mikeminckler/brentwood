<template>

    <div class="fixed w-full bottom-0 z-50 flex items-center border-t border-b bg-gray-200 p-2" v-if="editingEnabled">

        <div class="flex-1 flex">
            <div class=""><input type="text" v-model="page.name" @change="savePage()" /></div>
        </div>

        <div class="">
            <div class="button mx-2" @click="createSubPage">
                <div class="pr-2"><i class="fas fa-file-medical"></i></div>
                <div>Create Sub Page</div>
            </div>
        </div>
    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback'

    export default {

        mixins: [Feedback],
        props: ['currentPage'],
        data() {
            return {
                page: {
                    id: 0,
                    name: '',
                    parent_page_id: '',
                    order: 0,
                },
            }
        },

        computed: {
            editingEnabled() {
                return this.$store.state.editing;
            },
        },

        mounted() {
            /**
             * we need to take the current page we pass to this component
             * and set it as the page to edit so that the pages properties 
             * will be reactive
             */
            this.page = this.currentPage;
        },

        methods: {

            createSubPage: function() {

                let input = {
                    name: 'Untitled Page',
                    parent_page_id: this.page.id,
                    order: 1,
                }

                this.$http.post('/pages/create', input).then( response => {
                    this.processSuccess(response);
                    window.location = response.data.page.full_slug;
                }, error => {
                    this.processErrors(error.response);
                });
            },

            savePage: _.debounce(function() {

                let input = {
                    name: this.page.name,
                    parent_page_id: this.page.parent_page_id,
                    order: this.page.order,
                };

                this.$http.post('/pages/' + this.page.id, input).then( response => {
                    this.$eventer.$emit('refresh-page-tree');
                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                });

            }, 1000),
        },

    }
</script>
