<template>

    <div class="relative mt-8 form-content-element"
         :class="contentElement.pivot.unlisted ? 'bg-gray-200 opacity-75' : ''" 
        v-if="contentElement.id >= 1"
    >

        <div class="absolute text-xl flex flex-col items-center right-0" style="right: -40px">
            <div class="content-element-icons" @click="showAdd = !showAdd" title="Add Content Element After"><i class="fas fa-file-medical"></i></div>
            <div class="content-element-icons text-green-600 hover:text-green-500" title="Publish Now" @click="publishNow()" v-if="!isPublished"><i class="fas fa-sign-out-alt"></i></div>
            <div class="content-element-icons" :class="contentElement.publish_at ? 'text-green-600' : ''" title="Set Publish Date" @click="showPublishAt = !showPublishAt" v-if="!isPublished"><i class="fas fa-clock"></i></div>
            <div class="content-element-icons" v-if="contentElementIndex !== 0" @click="$emit('sortUp')" title="Move Up"><i class="fas fa-arrow-alt-circle-up"></i></div>
            <div class="content-element-icons" v-if="!last" @click="$emit('sortDown')" title="Move Down"><i class="fas fa-arrow-alt-circle-down"></i></div>
            <div class="content-element-icons" @click="contentElement.pivot.unlisted = 0" v-if="contentElement.pivot.unlisted" title="Hide Content"><i class="fas fa-eye"></i></div>
            <div class="content-element-icons" @click="contentElement.pivot.unlisted = 1" v-if="!contentElement.pivot.unlisted" title="Show Content"><i class="fas fa-eye-slash"></i></div>
            <div class="content-element-icons text-gray-800" @click="contentElement.pivot.expandable = 0" v-if="contentElement.pivot.expandable" title="Disable Expandable"><i class="fas fa-angle-double-down"></i></div>
            <div class="content-element-icons text-gray-400" @click="contentElement.pivot.expandable = 1" v-if="!contentElement.pivot.expandable" title="Make Expandable"><i class="fas fa-angle-double-down"></i></div>
            <div class="content-element-icons" title="Versioning/History?"><i class="fas fa-exchange-alt"></i></div>
            <div class="content-element-icons hover:text-primary" title="Remove Content" @click="removeContentElement()"><i class="fas fa-trash-alt"></i></div>
        </div>

        <div class="relative flex justify-end">
            <transition name="saving-icon">
                <div class="absolute z-6 flex text-green-600 bg-gray-100 px-4 py-2 border border-green-200 shadow" 
                    v-if="showSaving" 
                    key="saving"
                >
                    <div class="spin"><i class="fas fa-sync-alt"></i></div>
                    <div class="ml-2">SAVING</div>
                </div>
            </transition>

            <transition name="draft">
                <div class="absolute flex items-center z-5" v-if="!isPublished">
                    <div class="flex items-center bg-yellow-100 pl-2 border border-yellow-300">
                        <div class="font-bold relative">DRAFT</div>
                        <div class="remove-icon ml-2" @click="removeDraft()"><i class="fas fa-times"></i></div>
                    </div>
                </div>
            </transition>

            <div class="flex bg-gray-300 px-2 py-1" v-if="contentElement.pivot.unlisted">
                <div class=""><i class="fas fa-eye-slash"></i></div>
                <div class="ml-2">Hidden</div>
            </div>

            <div class="flex bg-orange-200 px-2 py-1" v-if="contentElement.pivot.expandable">
                <div class=""><i class="fas fa-angle-double-down"></i></div>
                <div class="ml-2">Expandable</div>
            </div>

            <div class="absolute z-4" v-if="showPublishAt">
                <date-time-picker v-model="contentElement.publish_at" :remove="true"></date-time-picker>
            </div>

        </div>

        <expander :expandable="contentElement.pivot.expandable" :uuid="contentElement.uuid">
            <div class="" style="min-height: 150px">
                <component :is="contentElement.type" 
                    :content="contentElement.content"
                    :uuid="contentElement.uuid"
                    :first="first"
                    :content-element-index="contentElementIndex"
                ></component>
            </div>
        </expander>

        <transition name="add-content">
            <add-content-element v-if="showAdd && !last" :sort-order="contentElement.pivot.sort_order" @selected="showAdd = false"></add-content-element>
        </transition>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';
    import ContentElements from '@/Mixins/ContentElements';

    export default {

        mixins: [Feedback, ContentElements],

        props: ['contentElement', 'first', 'contentElementIndex', 'last'],

        components: {
            'add-content-element': () => import(/* webpackChunkName: "add-content-element" */ '@/Components/AddContentElement.vue'),
            'text-block': () => import(/* webpackChunkName: "text-block" */ '@/Forms/TextBlock.vue'),
            'photo-block': () => import(/* webpackChunkName: "photo-block" */ '@/Forms/PhotoBlock.vue'),
            'quote': () => import(/* webpackChunkName: "quote" */ '@/Forms/Quote.vue'),
            'youtube-video': () => import(/* webpackChunkName: "youtube-video" */ '@/Forms/YoutubeVideo.vue'),
            'embed-code': () => import(/* webpackChunkName: "embed-code" */ '@/Forms/EmbedCode.vue'),
            'banner-photo': () => import(/* webpackChunkName: "banner-photo" */ '@/Forms/BannerPhoto.vue'),
            'blog-list': () => import(/* webpackChunkName: "blog-list" */ '@/Forms/BlogList.vue'),
            'date-time-picker': () => import(/* webpackChunkName: "date-time-picker" */ '@/Components/DateTimePicker.vue'),
        },

        data() {
            return {
                showPublishAt: false,
                showAdd: false,
                showSaving: false,
                changedFields: [],
                saveContent: _.debounce( function() {
                    if (this.filteredChangedFields.length) {
                        //console.log('CE: ' + this.contentElement.id);
                        console.log(this.filteredChangedFields);
                        this.saveContentElement();
                    }
                }, 1000),
                setShowSaving: _.debounce( function() {
                    this.showSaving = this.isSaving;
                }, 500),
            }
        },

        computed: {
            page() {
                return this.$store.state.page;
            },
            pivot() {
                return {
                    contentable_id: this.$store.state.page.id,
                    contentable_type: this.$store.state.page.type,
                };
            },
            contentElementClone() {
                return this.$lodash.cloneDeep(this.contentElement);
            },
            filteredChangedFields() {
                let ignore = [
                    'published_at',
                    'updated_at',
                    'created_at',
                    'id',
                    'content_id',
                    'version_id',
                    'content_element_id',
                    'small',
                    'medium',
                    'large',
                ];
                return this.changedFields.filter( f => {
                    return ignore.indexOf(f) < 0;
                });
            },
            isPublished() {
                let pivot = this.$lodash.find(this.contentElement.contentables, contentable => {
                    return contentable.contentable_id === this.page.id && contentable.contentable_type === this.page.full_type;
                });
                return pivot.version.published_at ? true : false;
            }
        },

        watch: {
            contentElementClone: {
                handler: function(newObject, oldObject) {
                    this.findDifferentProperties(newObject, oldObject);
                    // we need to put this here as its the main watcher
                    // we can't rely on watching the array as it only tracks the keys not the values
                    // the debouncer will take care of it's excessive firing
                    if (!this.preventChanges) {
                        this.saveContent();
                    }
                },
                deep: true,
            },

            isSaving() {
                if (this.isSaving) {
                    this.showSaving = true;
                } else {
                    this.setShowSaving();
                }
            },

        },

        mounted() {

            const listener = data => {
                if (data === this.contentElement.uuid) {
                    //console.log('SAVE CONTENT LISTENER');
                    this.saveContent();
                }
            };

            this.$eventer.$on('save-content', listener);

            this.$echo.private('role.2')
                .listen('ContentElementSaved', data => {
                    if (this.contentElement.uuid === data.content_element.uuid) {
                        console.log('LOAD FROM EVENT: ' + this.contentElement.uuid);
                        this.loadContentElement();
                    }
                });

            this.$once('hook:destroyed', () => {
                this.$eventer.$off('save-content', listener);
                this.$echo.leave('role.2');
            });

        },

        methods: {

            findDifferentProperties: function(newObject, oldObject) {
                 return this.changes(newObject, oldObject);
            },

            changes: function(newObject, oldObject) {

                return this.$lodash.transform(newObject, (result, value, key) => {
                    if (!this.$lodash.isEqual(value, oldObject[key])) {

                        let resultKey;

                        if (this.$lodash.isObject(value) && this.$lodash.isObject(oldObject[key])) {
                            if (key !== 'version') {
                                resultKey = this.changes(value, oldObject[key]);
                            }
                        } else {
                            resultKey = value;
                            console.log(key + '::' + value);
                            //console.log(typeof value);
                            if (!this.$lodash.includes(this.changedFields, key) && !this.preventChanges) {
                                this.changedFields.push(key);
                            }
                        }

                        result[key] = resultKey;
                    }
                });
                
            },

            loadContentElement: function() {

                let input = {
                    pivot: this.pivot,
                };

                this.$http.post('/content-elements/' + this.contentElement.id + '/load', input).then( response => {
                    this.preventChanges = true;
                    this.$emit('update', response.data.content_element);
                    this.$nextTick(() => {
                        this.changedFields = [];
                        this.preventChanges = false;
                    });
                }, error => {
                    this.processErrors(error.response);
                });
                
            },

            removeContentElement: function() {

                var answer = confirm('Are you sure you want to DELETE this content element?');
                if (answer == true) {

                    let input = {
                        remove_all: true,
                        pivot: this.pivot,
                    }

                    this.$http.post('/content-elements/' + this.contentElement.id + '/remove', input).then( response => {
                        this.$emit('remove');
                        this.processSuccess(response);
                    }, error => {
                        this.processErrors(error.response);
                    });

                }
            },

            removeDraft: function() {

                var answer = confirm('Are you sure you want to RESTORE to the current version?');
                if (answer == true) {

                    let input = {
                        pivot: this.pivot,
                    }

                    this.$http.post('/content-elements/' + this.contentElement.id + '/remove', input).then( response => {
                        if (response.data.content_element) {
                            this.$emit('update', response.data.content_element);
                        } else {
                            this.$emit('remove');
                        }
                        this.processSuccess(response);
                    }, error => {
                        this.processErrors(error.response);
                    });

                }
            },

            publishNow: function() {
                
                var answer = confirm('Are you sure you want to PUBLISH this content element?');
                if (answer == true) {

                    let input = {
                        pivot: this.pivot,
                    }

                    this.$http.post('/content-elements/' + this.contentElement.id + '/publish', input).then( response => {
                        this.processSuccess(response);
                        this.$eventer.$emit('load-page');
                    }, error => {
                        this.processErrors(error.response);
                    });

                }
            }

        },

    }
</script>

<style>

@keyframes draft {
    0% {
        opacity: 0;
        max-height: 0;
    }
    100%   {
        opacity: 1;
        max-height: 32px;
    }
}

.draft-enter-active {
    animation: draft var(--transition-time) ease-out;
}

.draft-leave-active {
    animation: draft var(--transition-time) reverse;
}

@keyframes saving-icon {
    0% {
        opacity: 0;
    }
    100%   {
        opacity: 1;
    }
}

.saving-icon-enter-active {
    animation: saving-icon var(--transition-time) ease-out;
}

.saving-icon-leave-active {
    animation: saving-icon var(--transition-time) reverse;
}

@keyframes add-content {
    0% {
        max-height: 0;
        opacity: 0;
        @apply my-0;
    }
    100%   {
        max-height: 65px;
        opacity: 1;
        @apply my-4;
    }
}

.add-content-enter-active {
    animation: add-content calc(var(--transition-time) * 2) ease-out;
}

.add-content-leave-active {
    animation: add-content calc(var(--transition-time) * 2) reverse;
}

</style>
