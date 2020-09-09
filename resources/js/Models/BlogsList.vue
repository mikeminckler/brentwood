<template>

    <div class="ignore" @click="$emit('selected', blog)">
        <div class="cursor-pointer py-1 px-2" :class="[blog.unlisted ? 'text-gray-500' : 'link']">{{ blog.name }}</div>
        <div class="py-1 px-2">
            <div class="" v-if="blog.unlisted" @click="hideBlog()"><i class="fas fa-eye"></i></div>
            <div class="" v-if="!blog.unlisted" @click="unhideBlog()"><i class="fas fa-eye-slash"></i></div>
        </div>
    </div>

</template>

<script>
    export default {

        props: ['item'],
        data() {
            return {
            
            }
        },

        computed: {
            blog() {
                return this.item;
            },
        },

        watch: {
        },

        mounted() {
        },

        methods: {

            hideBlog: function() {

            },

            unhideBlog: function() {

            },
        },

    }
</script>
