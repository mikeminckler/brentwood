<template>

    <div class="relative">

        <transition-group name="content-elements" tag="div" class="relative">
            <form-content-element 
                v-for="(contentElement, index) in sortedContentElements"
                :key="contentElement.uuid"
                :value="contentElement"
                @sortUp="sortUp(contentElement)"
                @sortDown="sortDown(contentElement)"
                @remove="removeContentElement(contentElement)"
                @update="updateContentElement(contentElement, $event)"
                :first="isFirst(contentElement)"
            >
            </form-content-element>
        </transition-group>

        <add-content-element 
            v-if="!contentElements.length"
            :expanded="true" 
            :sort-order="contentElements.length"
        ></add-content-element>

    </div>

</template>

<script>

    import ContentElement from '@/Forms/ContentElement.vue';
    import ContentElements from '@/Mixins/ContentElements';
    import Feedback from '@/Mixins/Feedback';
    import AddContentElement from '@/Components/AddContentElement.vue';

    export default {

        mixins: [Feedback, ContentElements],

        components: {
            'form-content-element': ContentElement,
            'add-content-element': AddContentElement,
        },

        computed: {
            contentElements() {
                return this.$store.state.page.content_elements;
            },
            sortedContentElements() {
                return this.$lodash.orderBy(this.contentElements, ['sort_order', 'id'], ['asc', 'asc']);
            },
        },

        mounted() {

            const listener = data => {
                this.addContentElement(data);
            };
            this.$eventer.$on('add-content-element', listener);

            this.$once('hook:destroyed', () => {
                this.$eventer.$off('add-content-element', listener);
            });
        },

        methods: {

            addContentElement: function(data) {

                this.$lodash.each(this.contentElements, ce => {
                    if (ce.sort_order >= data.sortOrder) {
                        ce.sort_order++;
                    }
                });

                this[data.type](data.sortOrder);
            },

            isFirst: function(contentElement) {

                return this.$lodash.filter(this.contentElements, ce => {
                    return contentElement.type === ce.type;
                })[0].id === contentElement.id;

            },

            sortUp: function(contentElement) {

                if (contentElement.sort_order > 1) {
                    let currentIndex = this.$lodash.findIndex(this.sortedContentElements, ce => {
                        return contentElement.id === ce.id;
                    });

                    let nextElement = this.sortedContentElements[currentIndex - 1];

                    if (nextElement) {
                        this.sortedContentElements[currentIndex - 1].sort_order = contentElement.sort_order;
                    }

                    contentElement.sort_order--;
                }
            },

            sortDown: function(contentElement) {

                let currentIndex = this.$lodash.findIndex(this.sortedContentElements, ce => {
                    return contentElement.id === ce.id;
                });

                let nextElement = this.sortedContentElements[currentIndex + 1];

                if (nextElement) {
                    this.sortedContentElements[currentIndex + 1].sort_order = contentElement.sort_order;
                }

                contentElement.sort_order++;
            },

            removeContentElement: function(contentElement) {
                let index = this.$lodash.findIndex( this.contentElements, ce => {
                    return ce.id === contentElement.id;
                });
                this.contentElements.splice( index, 1);
            },

            newContentElement: function(sortOrder) {
                return {
                    id: '0.' + this.contentElements.length,
                    page_id: this.$store.state.page.id,
                    sort_order: sortOrder,
                    unlisted: false,
                };
            },

            saveNewContentElement: function(contentElement) {
                this.saveContentElement(contentElement, true);
            },

            // Content Types

            addTextBlock: function(sortOrder) {

                let contentElement = this.newContentElement(sortOrder);

                contentElement.type = 'text-block';
                contentElement.content = {
                    id: 0,
                    header: '',
                    body: '<p></p>',
                };

                this.saveNewContentElement(contentElement);

            },

            addPhotoBlock: function(sortOrder) {
                
                let contentElement = this.newContentElement(sortOrder);

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

            addQuote: function(sortOrder) {

                let contentElement = this.newContentElement(sortOrder);

                contentElement.type = 'quote';
                contentElement.content = {
                    id: 0,
                    photos: [],
                    body: '<p></p>',
                    author_name: '',
                    author_details: '',
                };

                this.saveNewContentElement(contentElement);

            },

            addYoutubeVideo: function(sortOrder) {

                let contentElement = this.newContentElement(sortOrder);

                contentElement.type = 'youtube-video';
                contentElement.content = {
                    id: 0,
                    video_id: '',
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
