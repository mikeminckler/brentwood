<template>

    <div class="relative">

        <div class="absolute text-xl" style="right: -40px">
            <div class="content-element-icons" @click="contentElement.sort_order > 1 ? contentElement.sort_order-- : ''"><i class="fas fa-arrow-alt-circle-up"></i></div>
            <div class="content-element-icons" @click="contentElement.sort_order++"><i class="fas fa-arrow-alt-circle-down"></i></div>
        </div>

        <div class="absolute z-4">
            <transition name="fade">
                <div class="flex bg-gray-100 text-green-500 px-2 py-1" v-if="$store.state.saving.find( save => save === contentElement.id)" key="saving">
                    <div class="spin"><i class="fas fa-sync-alt"></i></div>
                    <div class="ml-2">Saving</div>
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

            saveContent: _.debounce( function() {
                this.queueSave();
            }, 1000),

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
