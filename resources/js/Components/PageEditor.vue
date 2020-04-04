<template>

    <div class="w-full z-20 flex items-center justify-center bg-gray-100 p-1" v-if="editing">

        <div class="w-full max-w-6xl flex items-center bg-gray-200 p-2 shadow">

            <div class="button mx-2" @click="createPage()">
                <div class=""><i class="fas fa-file-medical"></i></div>
            </div>

            <div class="button mx-2" @click="createSubPage()">
                <div class=""><i class="fas fa-file-download"></i></div>
            </div>

            <div class="button mx-2" @click="preview()">
                <div class=""><i class="fas fa-eye"></i></div>
            </div>

            <div class="flex items-center justify-center flex-1">
                <div class="form"><input type="text" v-model="page.name" @enter="savePage" @focus="$event.target.select()" @change="savePage()" /></div>
                <div class="">
                    <checkbox-input v-model="page.unlisted" @change="savePage()" label="Unlisted"></checkbox-input> 
                </div>

            </div>

            <transition name="saving">
                <div class="flex mx-2 bg-green-600 hover:bg-green-500 text-white px-4 py-1 font-bold cursor-pointer" 
                    @click="publishPage()"
                     v-if="hasDraft || page.can_be_published"
                >
                    <div class="pr-2"><i class="fas fa-sign-out-alt"></i></div>
                    <div>Publish</div>
                </div>
            </transition>

            <div class="flex px-4 mx-2 items-center cursor-pointer hover:bg-primary hover:text-white" @click="removePage()" v-if="page.id !== 1">
                <div class="pr-2 text-xl"><i class="fas fa-times"></i></div>
                <div>Delete Page</div>
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
            hasDraft() {
                return this.$lodash.filter(this.page.content_elements, content_element => {
                    return content_element.version_id !== this.page.published_version_id;
                }).length ? true : false;
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

            createPage: function() {

                let input = {
                    name: 'Untitled Page',
                    parent_page_id: this.page.parent_page_id,
                    unlisted: true,
                    sort_order: this.page.sort_order + 1,
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

            preview: function() {
                window.open(this.page.full_slug + '?preview=true', this.page.full_slug);
                window.opener.focus();
            },

            publishPage: function() {

                this.$http.post('/pages/' + this.page.id + '/publish').then( response => {
                    location.reload();
                    //this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                });

            },

            removePage: function() {
                var answer = confirm('Are you sure you want to delete this page?');
                if (answer == true) {

                    this.$http.post('/pages/' + this.page.id + '/remove').then( response => {
                        window.location.href = '/';
                        this.processSuccess(response);
                    }, error => {
                        this.processErrors(error.response);
                    });

                }
                
            },
        },

    }
</script>
