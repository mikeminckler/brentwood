<template>

    <div class="flex flex-col max-h-screen sticky top-0 bg-gray-200">
        <div class="flex-1 overflow-y-scroll">
            <saving-indicator></saving-indicator>
            <div class="py-2 px-4 flex">
                <div class="flex cursor-pointer items-center" 
                    @click="addPage = !addPage"
                     :class="addPage ? 'button-active' : 'button'"
                >
                    <div class="icon"><i class="fas fa-file-medical"></i></div>
                    <div class="pl-2">Add Page</div>
                </div>
            </div>
            <page-tree 
                :sort="true" 
                :expanded="false" 
                :show-changes="true" 
                max-height="100%"
                :insert="addPage ? true : false"
            ></page-tree>

            <div class="py-2">
                <slot></slot>
            </div>

        </div>
    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback.js';

    export default {

        mixins: [Feedback],
        
        components: {
            'saving-indicator': () => import(/* webpackChunkName: "saving-indicator" */ '@/Components/SavingIndicator'),
            'page-tree': () => import(/* webpackChunkName: "page-tree" */ '@/Components/PageTree'),
        },

        data() {
            return {
                addPage: false,
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {
        },

        methods: {
        },

    }
</script>
