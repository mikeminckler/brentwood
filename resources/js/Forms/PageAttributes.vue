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

        <form-tags v-model="tags"></form-tags>

    </div>

</template>

<script>
    export default {

        data() {
            return {
                tags: [],
            }
        },

        components: {
            'form-tags': () => import(/* webpackChunkName: "form-tags" */ '@/Forms/Tags.vue'),
        },

        computed: {
            page() {
                return this.$store.state.page;
            },
        },

        mounted() {
            this.setTags();
        },

        watch: {
            'page.tags': function() {
                this.setTags();
            },

            tags: function() {

                let pageTagIds = this.$lodash.map(this.page.tags, t => {
                    return t.id;
                });

                let tagIds = this.$lodash.map(this.tags, t => {
                    return t.id;
                });

                if (!this.$lodash.isEqual(pageTagIds, tagIds)) {
                    this.page.tags = this.tags;
                    this.$eventer.$emit('save-page');
                }

            }
        },

        methods: {

            setTags: function() {
                this.tags = this.$lodash.cloneDeep(this.page.tags);
            },

        },
        
    }
</script>
