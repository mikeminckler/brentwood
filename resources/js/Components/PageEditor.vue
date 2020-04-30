<template>

    <div class="w-full z-20 flex items-center justify-center bg-gray-100 p-1" v-if="editing && page">

        <div class="w-full max-w-6xl flex items-center bg-gray-200 p-2 shadow relative">

            <div class="button mx-2" @click="createPage()" v-if="page.id > 1">
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

            <div class="relative flex overflow-hidden">
                <transition name="slide-down">
                    <div class="flex mx-2 bg-green-600 hover:bg-green-500 text-white px-4 py-1 font-bold cursor-pointer w-32 justify-center" 
                        @click="publishPage()"
                         v-if="(hasDraft && page.can_be_published) && !$store.state.saving.length"
                    >
                        <div class="pr-2"><i class="fas fa-sign-out-alt"></i></div>
                        <div>Publish</div>
                    </div>
                </transition>

                <transition name="slide-down">
                    <div class="flex mx-2 text-green-600 px-4 py-1 w-32 justify-center" 
                         v-if="$store.state.saving.length"
                    >
                        <div class="spin"><i class="fas fa-sync-alt"></i></div>
                        <div class="ml-2">Saving</div>
                    </div>
                </transition>
            </div>

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
        props: ['currentPage'],
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

            const savePageEvent = event => {
                this.savePage();
            };
            this.$eventer.$on('save-page', savePageEvent);

            this.$once('hook:destroyed', () => {
                this.$eventer.$off('save-page', savePageEvent);
            });
        },

        methods: {

            createSubPage: function() {

                let input = {
                    name: 'Untitled Page',
                    parent_page_id: this.page.id,
                    unlisted: false,
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
                    unlisted: false,
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
                    footer_fg_file_upload: this.page.footer_fg_file_upload,
                    footer_bg_file_upload: this.page.footer_bg_file_upload,
                    footer_color: this.page.footer_color,
                };

                this.$store.dispatch('startSaving', 'page');

                this.$http.post('/pages/' + this.page.id, input).then( response => {
                    //this.$eventer.$emit('refresh-page-tree');
                    window.location = response.data.page.full_slug;
                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                });

            }, 750),

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
