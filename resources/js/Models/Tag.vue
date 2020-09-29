<template>

    <div class="tag pr-0">
        <div class="">{{ tag.name }}</div>
        <remove :dusk="'remove-tag-' + tag.id" @remove="$emit('remove', tag)" v-if="remove"></remove>
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
