<template>

    <div class="relative" :class="contentElement.unlisted ? 'bg-gray-200 opacity-75' : ''">

        <div class="absolute text-xl" style="right: -40px">
            <div class="content-element-icons" @click="$emit('sortUp')"><i class="fas fa-arrow-alt-circle-up"></i></div>
            <div class="content-element-icons" @click="$emit('sortDown')"><i class="fas fa-arrow-alt-circle-down"></i></div>
            <div class="content-element-icons" @click="contentElement.unlisted = false" v-if="contentElement.unlisted"><i class="fas fa-eye"></i></div>
            <div class="content-element-icons" @click="contentElement.unlisted = true" v-if="!contentElement.unlisted"><i class="fas fa-eye-slash"></i></div>
        </div>

        <div class="border bg-white p-2">ID:{{ contentElement.id }} : v{{ contentElement.version_id }} PREV:{{ contentElement.previous_id }}</div>

        <div class="absolute z-4">
            <transition name="fade">
                <div class="absolute flex bg-gray-100 text-green-500 px-2 py-1" v-if="$store.state.saving.find( save => save === contentElement.id)" key="saving">
                    <div class="spin"><i class="fas fa-sync-alt"></i></div>
                    <div class="ml-2">Saving</div>
                </div>
            </transition>

            <transition name="fade">
                <div class="flex bg-gray-300 px-2 py-1" v-if="contentElement.unlisted" key="unlisted">
                    <div class=""><i class="fas fa-eye-slash"></i></div>
                    <div class="ml-2">Hidden</div>
                </div>
            </transition>
        </div>

        <component :is="contentElement.type" 
            :content="contentElement.content"
        ></component>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';
    import TextBlock from '@/Forms/TextBlock.vue';
    import PhotoBlock from '@/Forms/PhotoBlock.vue';
    import ContentElements from '@/Mixins/ContentElements';

    export default {

        mixins: [Feedback, ContentElements],

        props: ['value'],

        components: {
            'text-block': TextBlock,
            'photo-block': PhotoBlock,
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

        },

    }
</script>
