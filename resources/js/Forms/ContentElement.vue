<template>

    <div class="relative mt-8" 
         :class="contentElement.pivot.unlisted ? 'bg-gray-200 opacity-75' : ''" 
        v-if="contentElement.id >= 1"
    >

        <div class="absolute text-xl flex flex-col items-center right-0" style="right: -40px">
            <div class="content-element-icons" :class="contentElement.publish_at ? 'text-green-600' : ''" title="Set Publish Date" @click="showPublishAt = !showPublishAt" v-if="!contentElement.published_at"><i class="fas fa-clock"></i></div>
            <div class="content-element-icons" @click="$emit('sortUp')" title="Move Up"><i class="fas fa-arrow-alt-circle-up"></i></div>
            <div class="content-element-icons" @click="$emit('sortDown')" title="Move Down"><i class="fas fa-arrow-alt-circle-down"></i></div>
            <div class="content-element-icons" @click="contentElement.pivot.unlisted = 0" v-if="contentElement.pivot.unlisted" title="Hide Content"><i class="fas fa-eye"></i></div>
            <div class="content-element-icons" @click="contentElement.pivot.unlisted = 1" v-if="!contentElement.pivot.unlisted" title="Show Content"><i class="fas fa-eye-slash"></i></div>
            <div class="content-element-icons text-gray-800" @click="contentElement.pivot.expandable = 0" v-if="contentElement.pivot.expandable" title="Disable Expandable"><i class="fas fa-angle-double-down"></i></div>
            <div class="content-element-icons text-gray-400" @click="contentElement.pivot.expandable = 1" v-if="!contentElement.pivot.expandable" title="Make Expandable"><i class="fas fa-angle-double-down"></i></div>
            <div class="content-element-icons" title="Versioning/History?"><i class="fas fa-exchange-alt"></i></div>
            <div class="remove-icon" title="Remove Content" @click="removeContentElement()"><i class="fas fa-times"></i></div>
        </div>

        <div class="relative flex justify-end">
            <transition name="saving-icon">
                <div class="flex bg-gray-100 absolute text-green-500 px-2 py-1 z-3" 
                    v-if="$store.state.saving.find( save => save === contentElement.id)" 
                    key="saving"
                >
                    <div class="spin"><i class="fas fa-sync-alt"></i></div>
                </div>
            </transition>

            <transition name="draft">
                <div class="absolute flex items-center z-3" v-if="!contentElement.published_at">
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
                ></component>
            </div>
        </expander>

        <add-content-element :sort-order="contentElement.pivot.sort_order"></add-content-element>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';
    import ContentElements from '@/Mixins/ContentElements';

    export default {

        mixins: [Feedback, ContentElements],

        props: ['contentElement', 'first'],

        components: {
            'add-content-element': () => import(/* webpackChunkName: "add-content-element" */ '@/Components/AddContentElement'),
            'text-block': () => import(/* webpackChunkName: "text-block" */ '@/Forms/TextBlock'),
            'photo-block': () => import(/* webpackChunkName: "photo-block" */ '@/Forms/PhotoBlock'),
            'quote': () => import(/* webpackChunkName: "quote" */ '@/Forms/Quote'),
            'youtube-video': () => import(/* webpackChunkName: "youtube-video" */ '@/Forms/YoutubeVideo'),
            'embed-code': () => import(/* webpackChunkName: "embed-code" */ '@/Forms/EmbedCode'),
            'banner-photo': () => import(/* webpackChunkName: "banner-photo" */ '@/Forms/BannerPhoto'),
            'date-time-picker': () => import(/* webpackChunkName: "date-time-picker" */ '@/Components/DateTimePicker.vue'),
        },

        data() {
            return {
                showPublishAt: false,
                changed: false,
                preventWatcher: false,
                saveContent: _.debounce( function() {
                    // refer to the mixin for saving of the content element
                    if (!this.preventWatcher && !this.isSaving) {
                        this.$nextTick(() => {
                            //console.log('SAVE CE: ' + this.contentElement.id);
                            this.saveContentElement();
                        });
                    } else {
                        //console.log('WATCHER PREVENTED');
                        this.preventWatcher = false;
                    }
                }, 500),
            }
        },

        watch: {
            contentElement: {
                handler: function(oldValue, newValue) {
                    // this gets tripped when the content is first loaded
                    // so we ignore the first watcher hit
                    if (!this.pageLoading) {
                        //console.log('WATCHER: ' + this.contentElement.id);
                        this.changed = true;
                        this.saveContent();
                    }
                },
                deep: true
            },
        },

        mounted() {

            const listener = data => {
                if (data === this.contentElement.uuid) {
                    this.saveContent();
                }
            };
            this.$eventer.$on('save-content', listener);

            this.$once('hook:destroyed', () => {
                this.$eventer.$off('save-content', listener);
                this.$echo.leave('role.2');
            });

            this.$echo.private('role.2')
                .listen('ContentElementSaved', data => {
                    if (this.contentElement.id === data.content_element.id) {
                        //console.log('LOAD CE: ' + this.contentElement.id);
                        this.preventWatcher = true;
                        this.loadContentElement();
                    }
                })

        },

        methods: {

            loadContentElement: function() {

                this.changed = false;
                this.$http.post('/content-elements/' + this.contentElement.id + '/load', {page_id: this.$store.state.page.id}).then( response => {
                    this.$emit('update', response.data.content_element);
                }, error => {
                    this.processErrors(error.response);
                });
                
            },

            removeContentElement: function() {

                var answer = confirm('Are you sure you want to delete this content element?');
                if (answer == true) {

                    this.$http.post('/content-elements/' + this.contentElement.id + '/remove', {remove_all: true, page_id: this.$store.state.page.id}).then( response => {
                        this.$emit('remove');
                        this.processSuccess(response);
                    }, error => {
                        this.processErrors(error.response);
                    });

                }
            },

            removeDraft: function() {

                var answer = confirm('Are you sure you want to restore to the current version?');
                if (answer == true) {

                    this.$http.post('/content-elements/' + this.contentElement.id + '/remove', {page_id: this.$store.state.page.id}).then( response => {
                        this.preventWatcher = true;
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
        max-width: 0;
    }
    100%   {
        opacity: 1;
        max-width: 32px;
    }
}

.saving-icon-enter-active {
    animation: saving-icon var(--transition-time) ease-out;
}

.saving-icon-leave-active {
    animation: saving-icon var(--transition-time) reverse;
}


</style>
