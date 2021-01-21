<template>

    <div class="first:ml-4 mr-4 rounded bg-white border border-gray-200 flex items-center h-8">
        <div class="px-2">{{ tag.name }}</div>
        <remove :dusk="'remove-tag-' + tag.id" @remove="$emit('remove', tag)" v-if="remove" :transparent="true"></remove>
    </div>

</template>

<script>
    export default {

        props: ['tag', 'remove'],

        components: {
            'remove': () => import(/* webpackChunkName: "remove" */ '@/Components/Remove'),
        },

    }
</script>
