<template>

    <div class="bg-white border border-gray-200 flex items-center h-8"
        :class="[flex ? 'first:ml-4 mr-4' : 'mb-2']"
        >
        <div class="flex-1 px-2">{{ tag.name }}</div>
        <remove :dusk="'remove-tag-' + tag.id" @remove="$emit('remove', tag)" v-if="remove" :transparent="true"></remove>
    </div>

</template>

<script>
    export default {

        props: ['tag', 'remove', 'flex'],

        components: {
            'remove': () => import(/* webpackChunkName: "remove" */ '@/Components/Remove'),
        },

    }
</script>
