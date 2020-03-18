<template>

    <div class="w-full z-20 flex items-center border-t border-b bg-gray-200 p-2" v-if="editing">

        <div class="flex-1 flex items-center">
            <div class=""><input type="text" v-model="page.name" @enter="savePage" @change="savePage()" /></div>
            <div class="">
                <checkbox-input v-model="page.unlisted" @change="savePage()" label="Unlisted"></checkbox-input> 
            </div>
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
    import CheckboxInput from '@/Components/CheckboxInput'

    export default {

        mixins: [Feedback],
        props: ['currentPage', 'editingEnabled'],
        data() {
            return {
            }
        },

        components: {
            'checkbox-input': CheckboxInput,
        },

        computed: {
            page() {
                return this.$store.state.page;
            },
            editing() {
                return this.$store.state.editing;
            },
        },

        mounted() {
            /**
             * we need to take the current page we pass to this component
             * and set it as the page to edit so that the pages properties 
             * will be reactive
             */
            this.$store.dispatch('setPage', this.currentPage);

            if (this.editingEnabled) {
                this.$store.dispatch('setEditing', this.editingEnabled);
            }
        },

        methods: {

            createSubPage: function() {

                let input = {
                    name: 'Untitled Page',
                    parent_page_id: this.page.id,
                    unlisted: true,
                    sort_order: this.page.pages.length + 1,
                    content_elements: [],
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
                    unlisted: this.page.unlisted ? true : false,
                    sort_order: this.page.sort_order,
                    content_elements: this.page.content_elements,
                };

                this.$http.post('/pages/' + this.page.id, input).then( response => {
                    //this.$eventer.$emit('refresh-page-tree');
                    window.location = response.data.page.full_slug;
                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                });

            }, 1000),
        },

    }
</script>
