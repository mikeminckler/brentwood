<template>

    <div class="relative mt-8 form-content-element border-dashed border-b border-gray-400"
         :class="contentElement.pivot.unlisted ? 'bg-gray-200 opacity-75' : ''" 
        v-if="contentElement.id >= 1"
    >

        <div class="absolute flex flex-col items-center right-0" style="right: -40px">
            <div class="button-icon" @click="showAdd = !showAdd" title="Add Content Element After"><i class="fas fa-file-medical"></i></div>
            <div class="button-icon mt-2 hover:text-green-600" title="Publish Now" @click="publishNow()" v-if="!isPublished"><i class="fas fa-sign-out-alt"></i></div>
            <div class="button-icon mt-2" :class="contentElement.publish_at ? 'text-green-600' : ''" title="Set Publish Date" @click="showPublishAt = !showPublishAt" v-if="!isPublished"><i class="fas fa-clock"></i></div>
            <div class="button-icon mt-2" v-if="contentElementIndex !== 0" @click="$emit('sortUp')" title="Move Up"><i class="fas fa-arrow-alt-circle-up"></i></div>
            <div class="button-icon mt-2" v-if="!last" @click="$emit('sortDown')" title="Move Down"><i class="fas fa-arrow-alt-circle-down"></i></div>
            <div class="button-icon toggle mt-2" @click="contentElement.pivot.unlisted = !contentElement.pivot.unlisted" :class="contentElement.pivot.unlisted ? 'active' : ''" :title="contentElement.pivot.unlisted ? 'Unhide Content' : 'Hide Content'"><i class="fas fa-eye"></i></div>
            <div class="button-icon toggle mt-2" @click="contentElement.pivot.expandable = !contentElement.pivot.expandable" :class="contentElement.pivot.expandable ? 'active' : ''" :title="contentElement.pivot.expandable ? 'Disable Expandable' : 'Make Expandable'"><i class="fas fa-angle-double-down"></i></div>
            <div class="button-icon mt-2 hidden" title="Versioning/History?"><i class="fas fa-exchange-alt"></i></div>
            <div class="button-icon mt-2" title="Remove Content" @click="removeContentElement()"><i class="fas fa-trash-alt"></i></div>
        </div>

        <div class="flex justify-end absolute w-full">
            <div class="flex bg-gray-200 px-2 relative z-4" v-if="contentElement.pivot.unlisted">
                <div class=""><i class="fas fa-eye-slash"></i></div>
                <div class="ml-2">Hidden</div>
            </div>

            <div class="flex bg-gray-200 px-2 relative z-4" v-if="contentElement.pivot.expandable">
                <div class=""><i class="fas fa-angle-double-down"></i></div>
                <div class="ml-2">Expandable</div>
            </div>

            <div class="absolute z-4" v-if="showPublishAt">
                <date-time-picker v-model="contentElement.publish_at" :remove="true"></date-time-picker>
            </div>

            <transition name="saving-icon">
                <div class="absolute z-6 flex text-green-600 bg-gray-100 px-2 border border-green-200 shadow" 
                    v-if="showSaving" 
                    key="saving"
                >
                    <div class="spin"><i class="fas fa-sync-alt"></i></div>
                    <div class="ml-2">Saving</div>
                </div>
            </transition>

            <transition name="draft">
                <div class="flex items-center relative z-4" v-if="!isPublished">
                    <div class="flex items-center bg-yellow-100 px-3 border border-yellow-300">
                        <div class="font-bold relative">Draft</div>
                        <div class="remove-icon ml-1" @click="removeDraft()"><i class="fas fa-times"></i></div>
                    </div>
                </div>
            </transition>


        </div>

        <expander :expandable="contentElement.pivot.expandable" :uuid="contentElement.uuid">
            <div class="" style="min-height: 350px">
                <component :is="contentElement.type" 
                    :content="contentElement.content"
                    :uuid="contentElement.uuid"
                    :first="first"
                    :content-element-index="contentElementIndex"
                ></component>
            </div>
        </expander>

        <transition name="form-tags">
            <div class="bg-gray-200 px-2 py-1">
                <form-tags v-model="contentElement.tags" placeholder="Add Tags" :flex="true"></form-tags>
            </div>
        </transition>

        <transition name="add-content">
            <add-content-element v-if="showAdd && !last" :sort-order="contentElement.pivot.sort_order + 1" @selected="showAdd = false" :border="true"></add-content-element>
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
            'form-tags': () => import(/* webpackChunkName: "form-tags" */ '@/Forms/Tags.vue'),
            'inquiry-form': () => import(/* webpackChunkName: "inquiry-form" */ '@/Forms/InquiryForm.vue'),
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
                    //'created_at',
                    //'id',
                    //'content_id',
                    //'version',
                    //'version_id',
                    //'content_element_id',
                    //'small',
                    //'medium',
                    //'large',
                    //'contentables',
                    //'contentable_id',
                    //'contentable_type',
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
                        // you can find this method in the mixin
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

            this.$echo.private('role.' + this.$store.state.page.type + 's-editor')
                .listen('ContentElementSaved', data => {
                    if (this.contentElement.uuid === data.content_element.uuid) {
                        console.log('LOAD FROM EVENT: ' + this.contentElement.uuid);
                        this.loadContentElement();
                    }
                });

            this.$once('hook:destroyed', () => {
                this.$eventer.$off('save-content', listener);
                this.$echo.leave('role.' + this.$store.state.page.type + 's-editor');
            });

        },

        methods: {

            findDifferentProperties: function(newObject, oldObject) {
                let changes = this.changes(newObject, oldObject);

                this.pushChangesKeys(changes);

                console.log('DETECTING CHANGES');
                console.log(this.changedFields);

            },

            pushChangesKeys: function(item) {

                this.$lodash.forOwn(item, (value, key) =>  {
                    if (this.$lodash.isObject(value)) {
                        this.pushChangesKeys(value);
                    } else {
                        if (!this.$lodash.includes(this.changedFields, key) && !this.preventChanges) {
                            this.changedFields.push(key);
                        }
                    }
                });

            },

            changes: function(newObject, oldObject) {

                return this.$lodash.transform(newObject, (result, value, key) => {
                    if (!this.$lodash.isEqual(value, oldObject[key])) {

                        let resultKey;

                        if (this.$lodash.isObject(value) && this.$lodash.isObject(oldObject[key])) {
                            resultKey = this.changes(value, oldObject[key]);
                        } else {
                            resultKey = value;
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
        @apply my-0 pt-0;
    }
    100%   {
        max-height: 65px;
        opacity: 1;
        @apply my-4 pt-4;
    }
}

.add-content-enter-active {
    animation: add-content calc(var(--transition-time) * 2) ease-out;
}

.add-content-leave-active {
    animation: add-content calc(var(--transition-time) * 2) reverse;
}

</style>
