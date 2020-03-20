<template>

    <div class="relative">

        <transition-group name="content-elements" tag="div" class="relative">
            <form-content-element 
                v-for="(contentElement, index) in sortedContentElments"
                :key="contentElement.type + '-' + contentElement.id"
                :value="contentElement"
            >
            </form-content-element>
        </transition-group>

        <div class="flex w-full bg-gray-200 p-2 relative z-2 shadow mt-4 items-center">
            <div class="font-semibold">Create New</div>
            <div class="button mx-2" @click="addTextBlock">
                <div class="pr-2"><i class="fas fa-align-justify"></i></div>
                <div>Text</div>
            </div>

            <div class="button mx-2" @click="addPhotoBlock">
                <div class="pr-2"><i class="fas fa-file-image"></i></div>
                <div>Photos</div>
            </div>
        </div>

    </div>

</template>

<script>

    import ContentElement from '@/Forms/ContentElement.vue';
    import ContentElements from '@/Mixins/ContentElements';
    import Feedback from '@/Mixins/Feedback';

    export default {

        mixins: [Feedback, ContentElements],

        components: {
            'form-content-element': ContentElement,
        },

        computed: {
            contentElements() {
                return this.$store.state.page.content_elements;
            },
            sortedContentElments() {
                return this.$lodash.orderBy(this.contentElements, ['sort_order', 'id'], ['asc', 'asc']);
            },
        },

        methods: {

            newContentElement: function() {
                return {
                    id: '0.' + this.$store.state.page.content_elements.length,
                    page_id: this.$store.state.page.id,
                    sort_order: this.$store.state.page.content_elements.length + 1,
                    unlisted: false,
                };
            },

            saveNewContentElement: function(contentElement) {
                this.saveContentElement(contentElement, true);
            },

            addTextBlock: function() {

                let contentElement = this.newContentElement();

                contentElement.type = 'text-block';
                contentElement.content = {
                    id: 0,
                    header: '',
                    body: '<p></p>',
                };

                this.saveNewContentElement(contentElement);

            },

            addPhotoBlock: function() {
                
                let contentElement = this.newContentElement();

                contentElement.type = 'photo-block';
                contentElement.content = {
                    id: 0,
                    photos: [],
                    columns: 1,
                    height: 33,
                    padding: false,
                    show_text: false,
                    header: '',
                    body: '',
                    text_order: 1,
                    text_span: 1,
                    text_style: '',
                };

                this.saveNewContentElement(contentElement);
            },
        },

    }
</script>

<style>

@keyframes content-elements {
    0% {
        opacity: 0;
        max-height: 0px;
    }
    100% {
        opacity: 1;
        max-height: 250px;
    }
}

.content-elements-enter-active {
    animation: content-elements var(--transition-time) ease-out;
}

.content-elements-leave-active {
    animation: content-elements var(--transition-time) reverse;
}

.content-elements-move {
    transition: transform var(--transition-time);
}

</style>
