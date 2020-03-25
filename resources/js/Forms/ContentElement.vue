<template>

    <div class="relative mt-8" :class="!contentElement.published_at ? '' : (contentElement.unlisted ? 'bg-gray-200 opacity-75' : '')" style="min-height: 150px;">

        <div class="absolute z-3 flex">
            <transition name="fade">
                <div class="absolute flex bg-gray-100 text-green-500 px-2 py-1" v-if="$store.state.saving.find( save => save === contentElement.id)" key="saving">
                    <div class="spin"><i class="fas fa-sync-alt"></i></div>
                    <div class="ml-2">Saving</div>
                </div>
            </transition>

            <div class="flex bg-gray-300 px-2 py-1" v-if="contentElement.unlisted">
                <div class=""><i class="fas fa-eye-slash"></i></div>
                <div class="ml-2">Hidden</div>
            </div>
        </div>

        <transition name="draft">
            <div class="relative bg-yellow-100 flex items-center z-2 justify-center" v-if="!contentElement.published_at">
                <div class="text-xl mr-2 relative"><i class="fas fa-pen-square"></i></div>
                <div class="font-bold relative">DRAFT</div>
                <div class="remove-icon mx-2" @click="removeDraft()"><i class="fas fa-times"></i></div>
            </div>
        </transition>

        <div class="absolute text-xl flex flex-col items-center" style="right: -40px">
            <div class="content-element-icons" @click="$emit('sortUp')"><i class="fas fa-arrow-alt-circle-up"></i></div>
            <div class="content-element-icons" @click="$emit('sortDown')"><i class="fas fa-arrow-alt-circle-down"></i></div>
            <div class="content-element-icons" @click="contentElement.unlisted = false" v-if="contentElement.unlisted"><i class="fas fa-eye"></i></div>
            <div class="content-element-icons" @click="contentElement.unlisted = true" v-if="!contentElement.unlisted"><i class="fas fa-eye-slash"></i></div>
            <div class="remove-icon" @click="removeContentElement()"><i class="fas fa-times"></i></div>
        </div>

        <component :is="contentElement.type" 
            :content="contentElement.content"
            :uuid="contentElement.uuid"
            :first="first"
        ></component>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';
    import TextBlock from '@/Forms/TextBlock.vue';
    import PhotoBlock from '@/Forms/PhotoBlock.vue';
    import Quote from '@/Forms/Quote.vue';
    import YoutubeVideo from '@/Forms/YoutubeVideo.vue';
    import ContentElements from '@/Mixins/ContentElements';

    export default {

        mixins: [Feedback, ContentElements],

        props: ['value', 'first'],

        components: {
            'text-block': TextBlock,
            'photo-block': PhotoBlock,
            'quote': Quote,
            'youtube-video': YoutubeVideo,
        },

        data() {
            return {
                contentElement: {},
                loaded: false,
                saveContent: _.debounce( function() {
                    this.queueSave();
                }, 1000),
            }
        },

        watch: {
            value() {
                this.contentElement = this.value;
            },
            contentElement: {
                handler: function(content) {
                    if (this.loaded) {
                        this.saveContent();
                    } else {
                        this.loaded = true;
                    }
                },
                deep: true
            },
        },

        mounted() {
            this.contentElement = this.value;
        },

        methods: {
            // refer to the mixin for saving of the content element

            queueSave: function() {
                if (this.$store.state.saving.find(save => save === this.contentElement.Id)) {
                    setTimeout(this.queueSave(), 500);
                } else {
                    this.saveContentElement();
                }
            },

            removeContentElement: function() {

                var answer = confirm('Are you sure you want to delete this content element?');
                if (answer == true) {

                    this.$http.post('/content-elements/' + this.contentElement.id + '/remove', {remove_all: true}).then( response => {
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

                    this.$http.post('/content-elements/' + this.contentElement.id + '/remove').then( response => {
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

</style>
