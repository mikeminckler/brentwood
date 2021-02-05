<template>


    <div class="flex mt-8">
        <div class="flex-1">
            <div class="px-8">
                <h1>Page Permissions</h1>

                <div class="my-4">
                    <page-tree 
                        :emit-event="true" 
                        @selected="selectedPage = $event.page"
                        :expanded="true"
                    ></page-tree>
                </div>
            </div>
        </div>

        <div class="flex-2">
            <form-permissions :item="selectedPage"></form-permissions>
        </div>
    </div>

</template>

<script>

    export default {

        components: {
            'page-tree': () => import(/* webpackChunkName: "page-tree" */ '@/Components/PageTree'),
            'form-permissions': () => import(/* webpackChunkName: "form-permissions" */ '@/Forms/Permissions.vue'),
        },

        data() {
            return {
                selectedPage: null,
            }
        },

    }
</script>
