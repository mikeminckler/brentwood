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

        <form-tags :tags="page.tags" @change="$eventer.$emit('save-page')"></form-tags>

    </div>

</template>

<script>
    export default {

        data() {
            return {
                saved: false,
            }
        },

        components: {
            'form-tags': () => import(/* webpackChunkName: "form-tags" */ '@/Forms/Tags.vue'),
        },

        computed: {
            page() {
                return this.$store.state.page;
            }
        },

        watch: {

            'page.tags': function() {
                if (!this.saved) {
                    this.$eventer.$emit('save-page');
                    this.saved = true;
                }
            },

        },
        
    }
</script>
