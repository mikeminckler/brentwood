<template>

    <div class="w-full">
        <div class="" v-if="$store.state.page.type === 'blog'">
            <input class="outline-none h1"
                type="text" 
                v-model="page.name" 
                placeholder="Blog Title" 
                @change="$eventer.$emit('save-page')"
            />

            <input class="outline-none"
                type="text" 
                v-model="page.author" 
                placeholder="Author" 
                @change="$eventer.$emit('save-page')"
            />
        </div>

        <form-tags></form-tags>

    </div>

</template>

<script>
    export default {

        computed: {
            page() {
                return this.$store.state.page;
            }
        },

    }
</script>
