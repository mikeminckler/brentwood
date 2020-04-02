<template>

    <div class="pl-2 border-l-2 border-gray-300" :class="[page.unlisted ? 'bg-gray-200' : '', !page.published_version_id ? 'text-gray-500 italic' : '']">
        <div class="flex hover:bg-white border-b border-gray-300 items-center">
            <div class="cursor-pointer w-3 mr-2 flex items-center justify-center caret text-lg leading-none" 
                :class="{ 'rotate90' : expand }"
                @click="expand = !expand" v-if="page.pages ? ( page.pages.length ? true : false ) : false"
            >
                <i class="fas fa-caret-right"></i>
            </div>
            <div class="cursor-pointer flex-1" :class="[page.pages ? ( page.pages.length ? '' : 'pl-3' ) : 'pl-3', page.unlisted ? 'text-gray-500' : '']" @click="selectPage()">{{ page.name }}</div>
            <div class="" v-if="page.unlisted" class="text-gray-400 pl-2"><i class="fas fa-eye-slash"></i></div>
            <div class="text-gray-600 pl-2 text-lg" v-if="showChanges && (!page.published_version_id || page.can_be_published)"><i class="fas fa-pen-square"></i></div>
            <div class="pl-2" v-if="showContentElements" @click="displayContentElements = !displayContentElements"><i class="fas fa-caret-square-down"></i></div>
        </div>

        <div class="pl-3" v-if="displayContentElements">
            <div class="flex hover:bg-white border-b border-gray-300 cursor-pointer" 
                 v-for="contentElement in page.preview_content_elements"
                :key="contentElement.uuid"
                 @click="$emit('selected', page.id + '#c-' + contentElement.uuid)"
            >
                <div class="truncate">
                    <strong>{{ contentElement.type }}</strong>: {{ contentElement.content.header }} {{ contentElement.content.body }}
                </div>
            </div>
        </div>

        <page-list v-for="p in page.pages" 
            :page="p" 
            :key="p.id" 
            v-if="expand"  
            :emit-event="emitEvent"
            :show-content-elements="showContentElements"
            :expanded="expanded"
            @selected="$emit('selected', $event)"
            @showContentElements="$emit('showContentElements', $event)"
        ></page-list>
    </div>

</template>

<script>
    export default {
        props: ['page', 'emitEvent', 'showContentElements', 'expanded', 'showChanges'],

        components: {
            'page-list': () => import(/* webpackChunkName: "page-list" */ '@/Components/PageList'),
        },

        data() {
            return {
                expand: false,
                displayContentElements: false,
            }
        },

        mounted() {
            this.expand = this.page.id === 1 || this.page.parent_id == 1 ? true : this.expanded;
        },

        methods: {

            selectPage: function() {
                if (this.emitEvent) {
                    this.$emit('selected', this.page.id);
                } else {
                    window.location.href = this.page.full_slug;
                }
            }
        },
    }
</script>

<style>

    @keyframes page-list {
        0% {
            max-height: 0px;
            opacity: 0;
        }
        100%   {
            max-height: 25px;
            opacity: 1;
        }
    }

    .page-list-enter-active {
        animation: page-list var(--transition-time) ease-out;
    }

    .page-list-leave-active {
        animation: page-list var(--transition-time) reverse;
    }

</style>
