<template>

    <div class="pl-4 relative w-full overflow-visible" 
        :id="'page' + page.id" 
        :data-page-id="page.id"
        :draggable="page.id > 1 ? 'true' : 'false'"
        @dragstart.stop="!insert ? startDrag($event, page) : null"
        :class="[
            page.parent_page_id > 0 && !insert ? 'cursor-move sort-item' : '',
            page.pages ? ( page.pages.length > 0 ? 'sort-container' : '') : '',
            page.unlisted ? '' : '', 
            !page.published_version_id ? 'text-gray-500 italic' : '', 
         ]"
    >

        <div
            v-if="page.id > 1 && sort && $store.state.dragging && !insert"
            class="absolute w-3/4 overflow-visible z-2"
            :class="[$store.state.dragging ? 'h-8' : 'h-0']"
            @drop.stop='onDrop($event)'
            @dragover.stop.prevent
            @dragenter.stop.prevent="hover = true"
            @dragleave.stop.prevent="hover = false"
        >
            <div class="relative overflow-visible h-0" v-if="hover" style="bottom: -13px;"><i class="fas fa-caret-right"></i></div>
        </div>

        <div class="hover:bg-white border-b border-gray-300 items-center relative z-1" 
            :class="page.id === $store.state.page.id ? 'bg-white text-black' : (showChanges && (!page.published_version_id || page.can_be_published) ? 'bg-yellow-100' : '')"
            @mouseenter="showInsert = true"
            @mouseleave="showInsert = false"
        >
            <div class="flex items-center">
                <div class="cursor-pointer w-3 mr-2 flex items-center justify-center caret text-lg leading-none" 
                    :class="{ 'rotate90' : expand }"
                    @click="expand = !expand" v-if="page.pages ? ( page.pages.length ? true : false ) : false"
                >
                    <i class="fas fa-caret-right"></i>
                </div>
                <div class="cursor-pointer flex-1 pr-4 whitespace-no-wrap" :class="[page.pages ? ( page.pages.length ? '' : 'pl-3' ) : 'pl-3', page.unlisted ? 'text-gray-500' : '']" @click="selectPage()">{{ page.name }}</div>
                <div class="" v-if="page.unlisted" class="text-gray-400 pl-2 text-sm"><i class="fas fa-eye-slash"></i></div>
                <div class="text-gray-700 px-1 text-sm" v-if="showChanges && (!page.published_version_id || page.can_be_published)"><i class="fas fa-file-alt"></i></div>
                <div class="text-xl px-2" v-if="showContentElements" @click="displayContentElements = !displayContentElements"><i class="fas fa-caret-square-down"></i></div>
            </div>

            <div class="relative z-2 overflow-visible" v-if="insert && showInsert">
                <div class="relative flex text-sm py-1">
                    <div class="ml-2 button" @click="addBelow()">
                        <div class=""><i class="fas fa-long-arrow-alt-down"></i></div>
                        <div class="pl-1">Below</div>
                    </div>
                    <div class="ml-2 button" @click="addInside()">
                        <div class=""><i class="fas fa-long-arrow-alt-right"></i></div>
                        <div class="pl-1">Inside</div>
                    </div>
                </div>
            </div>

        </div>

        <div class="pl-3" v-if="displayContentElements">
            <div class="hover:bg-white border-b border-gray-300 cursor-pointer overflow-hidden relative my-1" 
                 v-for="contentElement in page.preview_content_elements"
                 :key="contentElement.uuid"
                 style="max-height: 100px;"
                 @click="$emit('selected', {page: page, contentElement: contentElement})"
            >
                <div class="absolute right-0 top-0 z-4 bg-gray-100 text-sm px-1">{{ contentElement.type }}</div>
                <div class="relative">
                    <div class="absolute w-full h-full bg-transparent z-3"></div>
                    <div class="origin-top-left relative z-2"
                         style="transform: scale(0.25); max-width: 1152px; width: 100vw; max-height: 400px;"
                        >
                        <component :is="contentElement.type" 
                            :content="contentElement.content"
                            :uuid="contentElement.uuid"
                             @click.stop
                        ></component>
                    </div>
                </div>
            </div>
        </div>

        <transition-group name="page-sort">
            <page-list v-for="(p, index) in $lodash.orderBy(page.pages, ['sort_order'], ['asc'])" 
                :page="p" 
                :key="p.id" 
                v-if="expand"  
                :emit-event="emitEvent"
                :show-changes="showChanges"
                :show-content-elements="showContentElements"
                :expanded="expanded"
                :sort="sort"
                :insert="insert"
                @selected="$emit('selected', $event)"
                @showContentElements="$emit('showContentElements', $event)"
            ></page-list>
        </transition-group>


    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';

    export default {

        mixins: [Feedback],

        props: ['page', 'emitEvent', 'showContentElements', 'expanded', 'showChanges', 'sort', 'insert'],

        components: {
            'page-list': () => import(/* webpackChunkName: "page-list" */ '@/Components/PageList'),
            'add-content-element': () => import(/* webpackChunkName: "add-content-element" */ '@/Components/AddContentElement'),
            'text-block': () => import(/* webpackChunkName: "text-block" */ '@/Forms/TextBlock'),
            'photo-block': () => import(/* webpackChunkName: "photo-block" */ '@/Forms/PhotoBlock'),
            'quote': () => import(/* webpackChunkName: "quote" */ '@/Forms/Quote'),
            'youtube-video': () => import(/* webpackChunkName: "youtube-video" */ '@/Forms/YoutubeVideo'),
            'embed-code': () => import(/* webpackChunkName: "embed-code" */ '@/Forms/EmbedCode'),
            'banner-photo': () => import(/* webpackChunkName: "banner-photo" */ '@/Forms/BannerPhoto'),
        },

        data() {
            return {
                expand: false,
                displayContentElements: false,
                hover: false,
                showInsert: false,
            }
        },

        mounted() {
            this.expand = this.page.id === 1 || this.page.parent_id == 1 ? true : this.expanded;
        },

        methods: {

            selectPage: function() {
                if (this.insert) {
                    return null;
                }
                if (this.emitEvent) {
                    this.$emit('selected', {page: this.page});
                } else {
                    if (this.$store.state.editorMounted) {
                        this.$eventer.$emit('load-page', this.page);
                    } else {
                        window.location.href = this.page.full_slug;
                    }
                }
            },

            createNewPage: function(parent_page_id, sort_order) {

                let input = {
                    name: 'Untitled Page',
                    parent_page_id: parent_page_id,
                    unlisted: false,
                    show_sub_menu: false,
                    sort_order: sort_order,
                    content_elements: [],
                    footer_fg_photo: {},
                    footer_bg_photo: {},
                }

                this.$http.post('/pages/create', input).then( response => {
                    this.processSuccess(response);
                    window.location = response.data.page.full_slug;
                }, error => {
                    this.processErrors(error.response);
                });

            },

            addBelow: function() {
                this.createNewPage(this.page.parent_page_id, this.page.sort_order);
            },

            addInside: function() {
                this.createNewPage(this.page.id, this.page.pages.length + 1);
            },

            startDrag: function(event, page) {
                this.$store.dispatch('setDragging', true);
                event.dataTransfer.dropEffect = 'move';
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('page', JSON.stringify(page));
            },

            onDrop (event) {
                let page = JSON.parse(event.dataTransfer.getData('page'));

                let input = {
                    sort_order: this.page.sort_order + .5,
                    parent_page_id: this.page.parent_page_id,
                };

                this.$store.dispatch('startSaving', 'page-tree');
                this.$store.dispatch('setDragging', false);

                this.$http.post('/pages/' + page.id + '/sort', input).then( response => {
                    this.processSuccess(response);
                    this.$eventer.$emit('refresh-page-tree');
                    this.$store.dispatch('completeSaving', 'page-tree');
                }, error => {
                    this.processErrors(error.response);
                    this.$store.dispatch('completeSaving', 'page-tree');
                });
            }
        },
    }
</script>

<style>

@keyframes page-sort {
    0% {
        opacity: 0;
        max-height: 0;
    }
    100%   {
        opacity: 1;
        max-height: 24px;
    }
}

.page-sort-enter-active {
    animation: page-sort var(--transition-time) ease-out;
}

.page-sort-leave-active {
    animation: page-sort var(--transition-time) reverse;
}

.page-sort-move {
    transition: transform var(--transition-time) linear;
}

</style>
