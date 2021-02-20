<template>

    <transition name="page-editor" mode="out-in">
        <div class="relative w-full z-9 flex items-center justify-center" v-if="editing && page" :key="'page-' + page.id">

            <div class="w-full max-w-6xl relative">

                <div class="w-full flex items-center bg-gray-200 p-1 pr-0 shadow relative">

                    <div class="button-icon" 
                        @click="showPageVersions = !showPageVersions" 
                        v-if="activeVersion"
                        :class="[activeVersion.id === page.published_version_id ? 'text-green-500 border-green-300 bg-green-100' : '', !activeVersion.published_at ? 'bg-yellow-100 border-yellow-300' : '']"
                    >
                        <div v-if="activeVersion.published_at && activeVersion.id !== page.published_version_id"><i class="fas fa-history"></i></div>
                        <div v-if="activeVersion.id === page.published_version_id" title="Published"><i class="fas fa-check"></i></div>
                        <div v-if="!activeVersion.published_at" title="Draft"><i class="fas fa-drafting-compass"></i></div>
                    </div>

                    <div class="button-icon ml-2" @click="preview()" title="Preview">
                        <div class=""><i class="fas fa-binoculars"></i></div>
                    </div>

                    <div class="button-icon toggle ml-2" :class="page.unlisted ? 'active' : ''" title="Unlisted" @click="toggleUnlisted()">
                        <div class=""><i class="fas fa-eye-slash"></i></div>
                    </div>

                    <div class="button-icon toggle ml-2" :class="page.show_sub_menu ? 'active' : ''" title="Display Sub Menu" @click="toggleShowSubMenu()">
                        <div class=""><i class="fas fa-ellipsis-h"></i></div>
                    </div>

                    <div class="button-icon ml-2 toggle" :class="showTags ? 'active' : ''" @click="showTags = !showTags" title="Edit Tags">
                        <div class=""><i class="fas fa-tag"></i></div>
                    </div>

                    <div class="button-icon ml-2 hover:text-primary hover:border-primary" @click="removePage()" v-if="page.id !== 1" title="Delete Page">
                        <div class=""><i class="fas fa-trash-alt"></i></div>
                    </div>

                    <div class="flex items-center flex-1 px-2">
                        <div class="form"><input type="text" v-model="page.name" @enter="savePage" @focus="$event.target.select()" @change="savePage()" /></div>
                        <div class="form ml-2" v-if="$store.getters.hasRole('admin')"><input type="text" v-model="page.title" @enter="savePage" @focus="$event.target.select()" @change="savePage()" placeholder="Meta Title"/></div>
                    </div>


                    <div class="relative flex items-center overflow-hidden">
                        
                        <transition name="saving">
                            <div class="flex bg-green-600 hover:bg-green-500 text-white font-bold cursor-pointer justify-center items-center h-8 overflow-visible w-32" 
                                 key="saved"
                                 v-if="(hasDraft && page.editable) && !saving"
                            >
                                <div class="flex flex-1 h-full items-center pr-2" @click="publishPage()">
                                    <transition name="slide">
                                        <div class="pl-2" v-if="!showPagePublishAt"><i class="fas fa-sign-out-alt"></i></div>
                                    </transition>
                                    <div class="pl-2">Publish</div>
                                </div>

                                <div class="relative h-full flex items-center">
                                    <div class="px-2 h-full flex items-center bg-green-500" v-if="!showPagePublishAt" @click.stop="showPagePublishAt = !showPagePublishAt"><i class="fas fa-clock"></i></div>
                                    <div class="px-2 bg-white h-full text-green-500 flex items-center" v-if="showPagePublishAt" @click="savePage()"><i class="fas fa-save"></i></div>
                                    <div class="px-2 bg-white text-primary h-full flex items-center" v-if="showPagePublishAt" @click="removePublishAt()"><i class="fas fa-times"></i></div>
                                </div>

                                <div class="absolute right-0 mt-8 top-0 z-3" v-show="showPagePublishAt">
                                    <date-time-picker v-model="page.publish_at" ></date-time-picker>
                                </div>

                            </div>

                            <div class="flex text-green-600 px-4 py-1 w-32 justify-center" v-if="saving" key="saving">
                                <div class="spin"><i class="fas fa-sync-alt"></i></div>
                                <div class="ml-2">Saving</div>
                            </div>
                        </transition>

                    </div>

                </div>

                <div class="z-2 absolute text-sm px-2 py-1 bottom-0 right-0 -mb-6 bg-white shadow" v-if="page.publish_at">Publishing {{ displayDateTime(page.publish_at) }}</div>

                <transition name="form-tags">
                    <div class="bg-gray-300 px-2 py-1" v-if="showTags || page.type === 'blog'">
                        <form-tags v-model="tags" placeholder="Add Page Tags" :flex="true"></form-tags>
                    </div>
                </transition>

                <div v-if="showPageVersions" class="absolute flex overflow-visible" style="top: 42px;">
                    <div class="relative bg-white shadow px-2 py-1">
                        <div class="flex px-2 hover:bg-gray-200 cursor-pointer" :class="version.id == activeVersion.id ? 'bg-gray-200' : ''" v-for="version in $lodash.orderBy(page.versions, ['id'], ['desc'])" @click="loadVersion(version)">
                            <div class="pr-2">v{{ version.name}}</div>
                            <div class="flex-1">{{ $moment(version.published_at ? version.published_at : version.updated_at).format('ddd YY-M-d h:mma') }}</div>
                            <div class="">
                                <div class="pl-2" v-if="version.published_at && version.id !== page.published_version_id" title="Load"><i class="fas fa-history"></i></div>
                                <div class="pl-2 text-green-500" v-if="version.id === page.published_version_id" title="Published"><i class="fas fa-check"></i></div>
                                <div class="pl-2" v-if="!version.published_at" title="Draft"><i class="fas fa-drafting-compass"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </transition>

</template>

<script>

    import Feedback from '@/Mixins/Feedback'
    import Dates from '@/Mixins/Dates';
    import Saving from '@/Mixins/Saving';

    export default {

        mixins: [Feedback, Dates, Saving],
        props: ['currentPage', 'resource'],

        data() {
            return {
                showPageVersions: false,
                showPagePublishAt: false,
                showTags: false,
                tags: [],
            }
        },

        components: {
            'checkbox-input': () => import(/* webpackChunkName: "checkbox-input" */ '@/Components/CheckboxInput.vue'),
            'date-time-picker': () => import(/* webpackChunkName: "date-time-picker" */ '@/Components/DateTimePicker'),
            'form-tags': () => import(/* webpackChunkName: "form-tags" */ '@/Forms/Tags.vue'),
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
                    let pivot = this.$lodash.find(content_element.contentables, contentable => {
                        return contentable.contentable_id === this.page.id && contentable.contentable_type === this.page.full_type;
                    });
                    return pivot.version.published_at ? false : true;
                }).length ? true : false;
            },
            activeVersion() {
                let uri = window.location.search.substring(1);
                let params = new URLSearchParams(uri);
                let versionId = params.get("version_id");

                if (versionId) {
                    return this.$lodash.find(this.page.versions, version => {
                        return version.id == versionId;
                    });
                } else {
                    return this.$lodash.last(this.page.versions);
                }
            },
        },

        mounted() {
            /**
             * we need to take the current page we pass to this component
             * and set it as the page to edit so that the pages properties 
             * will be reactive
             */

            this.$store.dispatch('editorMounted', true);

            if (this.currentPage) {
                this.$store.dispatch('setPage', this.currentPage);
            }

            const savePageEvent = event => {
                console.log('SAVE PAGE LOCAL');
                this.savePage();
            };
            this.$eventer.$on('save-page', savePageEvent);

            // load page from history state and side menu link
            const loadPageEvent = slug => {
                console.log('LOAD PAGE LOCAL');
                this.loadPage(slug);
            };
            this.$eventer.$on('load-page', loadPageEvent);

            this.$once('hook:destroyed', () => {
                this.$eventer.$off('save-page', savePageEvent);
                this.$eventer.$off('load-page', loadPageEvent);
                this.$echo.leave('role.' + this.$store.state.page.type + 's-editor');
            });

            this.$echo.private('role.' + this.$store.state.page.type + 's-editor')
                .listen('PageSaved', data => {
                    if (this.page.id === data.page.id) {
                        console.log('LOAD PAGE FROM PAGE SAVED');
                        this.loadPage();
                    }
                })
                .listen('PageDraftCreated', data => {
                    if (this.page.id === data.page.id) {
                        console.log('PAGE DRAFT CREATED EVENT');
                        this.page.versions = data.page.versions;
                    }
                })
                .listen('ContentElementCreated', data => {
                    if (this.page.id === data.page.id) {
                        console.log('LOAD PAGE FROM CONTENT ELEMENT CREATED');
                        this.loadPage();
                    }
                })
                .listen('ContentElementRemoved', data => {
                    if (this.page.id === data.page.id) {
                        console.log('LOAD PAGE FROM CONTENT ELEMENT REMOVED');
                        this.loadPage();
                    }
                });
        },

        unmounted() {
            this.$store.dispatch('editorMounted', false);
        },

        watch: {
            'page.tags': function() {
                this.setTags();
            },

            tags: function() {

                let pageTagIds = this.$lodash.map(this.page.tags, t => {
                    return t.id;
                });

                let tagIds = this.$lodash.map(this.tags, t => {
                    return t.id;
                });

                if (!this.$lodash.isEqual(pageTagIds, tagIds)) {
                    this.page.tags = this.tags;
                    this.$eventer.$emit('save-page');
                }

            }
        },

        methods: {

            setTags: function() {
                this.tags = this.$lodash.cloneDeep(this.page.tags);
            },

            loadPage: function(slug) {
                this.$store.dispatch('setPageLoading', true);

                if (!slug) {
                    slug = this.page.full_slug;
                } else if (this.$lodash.isObject(slug)) {
                    slug = slug.full_slug;
                }

                this.$http.get(slug).then( response => {
                    this.$store.dispatch('setPage', response.data.page);

                    let pathname = document.location.pathname;
                    if (pathname !== '/') {
                        pathname = pathname.substr(1);
                    }
                    let url = pathname + document.location.search;

                    if (response.data.page.full_slug !== url) {
                        //console.log('PUSH: ' + response.data.page.full_slug);
                        window.history.pushState(null, response.data.page.name, response.data.page.full_slug);
                    }

                    this.$nextTick(() => {
                        this.$store.dispatch('setPageLoading', false);
                    });
                }, error => {
                    this.processErrors(error.response);
                });
            },

            savePage: _.debounce(function() {

                let input = {
                    name: this.page.name,
                    title: this.page.title,
                    author: this.page.author,
                    tags: this.page.tags,
                    parent_page_id: this.page.parent_page_id,
                    unlisted: this.page.unlisted ? true : false,
                    show_sub_menu: this.page.show_sub_menu ? true : false,
                    sort_order: this.page.sort_order,
                    content_elements: this.page.content_elements,
                    footer_fg_photo: this.page.footer_fg_photo,
                    footer_bg_photo: this.page.footer_bg_photo,
                    footer_color: this.page.footer_color,
                    publish_at: this.page.publish_at,
                };

                this.$store.dispatch('startSaving', 'page');
                this.$store.dispatch('setPageLoading', true);

                this.$http.post('/' + this.resource + '/' + this.page.id, input).then( response => {
                    this.$eventer.$emit('refresh-page-tree');

                    this.$store.dispatch('setPage', response.data.page);
                    this.processSuccess(response);
                    this.$store.dispatch('completeSaving', 'page');

                    this.$nextTick(() => {
                        this.$store.dispatch('setPageLoading', false);
                    });

                    this.showPagePublishAt = false;

                    let pathname = document.location.pathname;
                    if (pathname !== '/') {
                        pathname = pathname.substr(1);
                    }
                    let url = pathname + document.location.search;

                    if (response.data.page.full_slug !== url) {
                        //console.log('REPLACE: ' + response.data.page.full_slug);
                        window.history.replaceState(null, response.data.page.name, response.data.page.full_slug);
                    }

                }, error => {
                    this.processErrors(error.response);
                    this.$store.dispatch('completeSaving', 'page');
                });

            }, 500),

            preview: function() {
                window.open(this.page.full_slug + '?preview=true', this.page.full_slug);
                if (window.opener) {
                    window.opener.focus();
                }
            },

            publishPage: function() {

                var answer = confirm('Are you sure you want to PUBLISH this page?');
                if (answer == true) {

                    this.$store.dispatch('setPageLoading', true);

                    this.$http.post('/' + this.resource + '/' + this.page.id + '/publish').then( response => {
                        console.log('PAGE PUBLISH COMPLETE');
                        this.$eventer.$emit('refresh-page-tree');
                        console.log('LOAD PAGE FROM PAGE PUBLISHED LOCAL');
                        this.loadPage();

                        //location.reload();
                        //console.log('SET PAGE AFTER PUBLISH');
                        //this.$store.dispatch('setPage', response.data.page);
                        //this.processSuccess(response);
                        //this.$nextTick(() => {
                        //    this.$store.dispatch('setPageLoading', false);
                        //});
                    }, error => {
                        this.processErrors(error.response);
                    });
                }

            },

            removePage: function() {
                var answer = confirm('Are you sure you want to delete this page?');
                if (answer == true) {

                    this.$http.post('/' + this.resource + '/' + this.page.id + '/remove').then( response => {
                        window.location.href = '/';
                        this.processSuccess(response);
                    }, error => {
                        this.processErrors(error.response);
                    });

                }
                
            },

            loadVersion: function(version) {
                if (version.published_at) {
                    window.location = this.page.full_slug + '?version_id=' + version.id;
                } else {
                    window.location = this.page.full_slug;
                }
            },

            removePublishAt: function() {
                this.page.publish_at = null;
                console.log('SAVE PAGE FROM REMOVE PUBLISH AT');
                this.savePage();
            },

            toggleUnlisted: function() {
                this.page.unlisted = !this.page.unlisted;
                this.savePage();
            },

            toggleShowSubMenu: function() {
                this.page.show_sub_menu = !this.page.show_sub_menu;
                this.savePage();
            }

        },

    }
</script>

<style>

@keyframes form-tags {
    0% {
        max-height: 0;
        opacity: 0;
        @apply py-0;
    }
    100%   {
        max-height: 50px;
        opacity: 1;
        @apply py-1;
    }
}

.form-tags-enter-active {
    animation: form-tags var(--transition-time) ease-out;
    overflow: hidden;
}

.form-tags-leave-active {
    animation: form-tags var(--transition-time) reverse;
    overflow: hidden;
}


@keyframes page-editor {
    0% {
        max-height: 0;
        opacity: 0;
        @apply py-0;
    }
    100%   {
        max-height: 42px;
        opacity: 1;
        @apply py-1;
    }
}

.page-editor-enter-active {
    animation: page-editor var(--transition-time) ease-out;
    overflow: hidden;
}

.page-editor-leave-active {
    animation: page-editor var(--transition-time) reverse;
    overflow: hidden;
}

</style>
