<template>

    <autocomplete
        url="/tags/search"
        v-model="tags"
        name="tags"
        :multiple="true"
        dusk="page-tags"
        @remove="removeTag($event)"
        :placeholder="placeholder ? placeholder : 'Add Tag'"
        :hideLabel="true"
        :no-margin="true"
        model="tag"
        :flex="flex"
        :add="true"
        add-url="/tags/create"
    ></autocomplete>

</template>

<script>
    export default {

        props: ['value', 'placeholder', 'flex'],

        components: {
            'autocomplete': () => import(/* webpackChunkName: "autocomplete" */ '@/Components/Autocomplete'),
        },

        data() {
            return {
                tags: [],
            }
        },

        computed: {
        },

        watch: {
            value() {
                this.tags = this.value;
            },
            tags() {
                this.$emit('input', this.tags);
            }
        },

        mounted() {
            if (this.value) {
                this.tags = this.value;
            }
        },

        methods: {

            removeTag: function(tag) {

                let index = this.$lodash.findIndex(this.tags, t => {
                    return t.id === tag.id;
                });
                this.tags.splice(index, 1);

            }

        },


    }
</script>
