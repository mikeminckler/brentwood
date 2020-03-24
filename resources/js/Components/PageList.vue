<template>

    <div class="px-2 py-1 border-l-2 border-gray-300" :class="[page.unlisted ? 'bg-gray-200' : '', !page.published_version_id ? 'text-gray-500 italic' : '']">
        <div class="flex">
            <div class="cursor-pointer w-3 mr-2 flex items-center justify-center caret text-lg leading-none" 
                 :class="{ 'rotate90' : expanded }"
                @click="expanded = !expanded" v-if="page.pages ? ( page.pages.length ? true : false ) : false"
            >
                <i class="fas fa-caret-right"></i>
            </div>
            <div class="cursor-pointer flex-1" :class="[page.pages ? ( page.pages.length ? '' : 'pl-3' ) : 'pl-3', page.unlisted ? 'text-gray-500' : '']" @click="goToPage(page)">{{ page.name }}</div>
            <div class="" v-if="page.unlisted" class="text-gray-400 pl-2"><i class="fas fa-eye-slash"></i></div>
            <div class="" v-if="!page.published_version_id || page.can_be_published" class="text-gray-600 pl-2 text-lg"><i class="fas fa-pen-square"></i></div>
        </div>
        <transition-group name="page-list">
            <page-list :page="p" :key="p.id" v-if="expanded" v-for="p in page.pages"></page-list>
        </transition-group>
    </div>

</template>

<script>
    export default {
        props: ['page'],

        components: {
            'page-list': () => import(/* webpackChunkName: "page-list" */ '@/Components/PageList'),
        },

        data() {
            return {
                expanded: true,
            }
        },

        methods: {

            goToPage: function(page) {
                window.location = page.full_slug;
            }

        },
    }
</script>

<style>

    @keyframes page-list {
        0% {
            max-height: 0px;
            opacity: 0;
            @apply pt-0;
        }
        100%   {
            max-height: 50px;
            opacity: 1;
            @apply pt-1;
        }
    }

    .page-list-enter-active {
        animation: page-list var(--transition-time) ease-out;
    }

    .page-list-leave-active {
        animation: page-list var(--transition-time) reverse;
    }

</style>
