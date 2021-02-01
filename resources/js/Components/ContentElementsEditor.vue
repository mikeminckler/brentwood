<template>

    <div class="relative">

        <transition-group name="content-elements" tag="div" class="relative mt-8 first:mt-0">
            <form-content-element 
                v-for="(contentElement, index) in sortedContentElements"
                :id="'c-' + contentElement.uuid"
                :key="contentElement.uuid"
                :content-element="contentElement"
                @sortUp="sortUp(contentElement)"
                @sortDown="sortDown(contentElement)"
                @remove="removeContentElement(contentElement)"
                @update="updateContentElement(contentElement, $event)"
                :first="isFirst(contentElement)"
                :last="index + 1 === contentElements.length"
                :content-element-index="index"
            >
            </form-content-element>
        </transition-group>

        <add-content-element 
            :sort-order="contentElements.length"
        ></add-content-element>

        <div class="" v-if="showPageTree">

            <page-tree
                :emit-event="true" 
                @selected="saveInstance($event)" 
                :show-content-elements="true" 
                :expanded="false"
            ></page-tree>
            
        </div>

    </div>

</template>

<script>

    import ContentElements from '@/Mixins/ContentElements';
    import Feedback from '@/Mixins/Feedback';

    export default {

        mixins: [Feedback, ContentElements],

        components: {
            'form-content-element': () => import(/* webpackChunkName: "form-content-element" */ '@/Forms/ContentElement.vue'),
            'add-content-element': () => import(/* webpackChunkName: "add-content-element" */ '@/Components/AddContentElement.vue'),
            'page-tree': () => import(/* webpackChunkName: "page-tree" */ '@/Components/PageTree.vue'),
        },

        data() {
            return {
                showPageTree: false,
                sortOrder: 1,
            }
        },

        computed: {
            page() {
                return this.$store.state.page;
            },
            contentElements() {
                return this.$store.state.page.content_elements;
            },
            sortedContentElements() {
                return this.$lodash.orderBy(this.contentElements, ['pivot.sort_order', 'id'], ['asc', 'asc']);
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
                    if (ce.pivot.sort_order >= data.sortOrder) {
                        ce.pivot.sort_order++;
                    }
                });

                this.sortOrder = data.sortOrder;
                this[data.type]();
            },

            isFirst: function(contentElement) {

                return this.$lodash.filter(this.contentElements, ce => {
                    return contentElement.type === ce.type;
                })[0].id === contentElement.id;

            },

            sortUp: function(contentElement) {

                if (contentElement.pivot.sort_order > 0) {
                    let currentIndex = this.$lodash.findIndex(this.sortedContentElements, ce => {
                        return contentElement.id === ce.id;
                    });

                    let nextElement = this.sortedContentElements[currentIndex - 1];

                    if (nextElement) {
                        this.sortedContentElements[currentIndex - 1].pivot.sort_order = contentElement.pivot.sort_order;
                    }

                    contentElement.pivot.sort_order--;
                }
            },

            sortDown: function(contentElement) {

                let currentIndex = this.$lodash.findIndex(this.sortedContentElements, ce => {
                    return contentElement.id === ce.id;
                });

                let nextElement = this.sortedContentElements[currentIndex + 1];

                if (nextElement) {
                    this.sortedContentElements[currentIndex + 1].pivot.sort_order = contentElement.pivot.sort_order;
                }

                contentElement.pivot.sort_order++;
            },

            removeContentElement: function(contentElement) {
                let index = this.$lodash.findIndex( this.contentElements, ce => {
                    return ce.id === contentElement.id;
                });
                this.contentElements.splice( index, 1);
            },

            newContentElement: function() {
                return {
                    id: '0.' + this.contentElements.length,
                    pivot: {
                        contentable_id: this.$store.state.page.id,
                        contentable_type: this.$store.state.page.type,
                        sort_order: this.sortOrder,
                        unlisted: 0,
                        expandable: 0,
                    },
                    tags: [],
                };
            },

            saveNewContentElement: function(contentElement) {
                this.saveContentElement(contentElement, true);
            },

            // Content Types

            addTextBlock: function() {

                let contentElement = this.newContentElement();

                contentElement.type = 'text-block';
                contentElement.content = {
                    id: 0,
                    header: '',
                    body: '<p></p>',
                    full_width: false,
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
                    padding: 0,
                    show_text: 0,
                    header: '',
                    body: '',
                    text_order: 1,
                    text_span: 1,
                    text_style: '',
                };

                this.saveNewContentElement(contentElement);
            },

            addQuote: function() {

                let contentElement = this.newContentElement();

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

            addYoutubeVideo: function() {

                let contentElement = this.newContentElement();

                contentElement.type = 'youtube-video';
                contentElement.content = {
                    id: 0,
                    video_id: '',
                    full_width: this.sortOrder === 1 ? true : false,
                };

                this.saveNewContentElement(contentElement);

            },

            addEmbedCode: function() {

                let contentElement = this.newContentElement();

                contentElement.type = 'embed-code';
                contentElement.content = {
                    id: 0,
                    code: '',
                };

                this.saveNewContentElement(contentElement);

            },

            addBannerPhoto: function() {

                let contentElement = this.newContentElement();

                contentElement.type = 'banner-photo';
                contentElement.content = {
                    id: 0,
                    body: '<p></p>',
                    header: '',
                };

                this.saveNewContentElement(contentElement);

            },

            addBlogList: function() {

                let contentElement = this.newContentElement();

                contentElement.type = 'blog-list';
                contentElement.content = {
                    id: 0,
                    header: '',
                    tags: [],
                };

                this.saveNewContentElement(contentElement);

            },

            addInquiryForm: function() {

                let contentElement = this.newContentElement();

                contentElement.type = 'inquiry-form';
                contentElement.content = {
                    id: 0,
                    header: '',
                    body: '',
                    show_student_info: false,
                    show_interests: false,
                    show_livestreams: false,
                    show_livestreams_first: false,
                    tags: [],
                };

                this.saveNewContentElement(contentElement);

            },

            addInstance: function() {
                this.showPageTree = !this.showPageTree;
            },

            saveInstance: function(linkData) {
                if (linkData.contentElement) {
                    this.showPageTree = false;

                    console.log('CLONE: ' + linkData.contentElement.id);
                    let contentElement = this.$lodash.cloneDeep(linkData.contentElement);
                    contentElement.pivot = this.newContentElement().pivot;
                    contentElement.instance = true;
                    this.saveContentElement(contentElement, true);
                }
            }

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
        max-height: 325px;
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
