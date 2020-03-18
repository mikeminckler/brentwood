<template>

    <div class="">

        <component :is="contentElement.type" 
            :content="contentElement.content"
            @save="saveContentElement()"
        ></component>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';
    import TextBlock from '@/Forms/TextBlock.vue';

    export default {

        mixins: [Feedback],

        props: ['value'],

        components: {
            'text-block': TextBlock,
        },

        data() {
            return {
                contentElement: {},
            }
        },

        computed: {
        },

        watch: {
            value() {
                this.contentElement = this.value;
            }
        },

        mounted() {
            this.contentElement = this.value;
        },

        methods: {

            saveContentElement: function() {

                let url = '/content-elements/' + ( this.contentElement.id >= 1 ? this.contentElement.id : 'create');

                this.$http.post(url, this.contentElement).then( response => {
                    this.$emit('input', response.data.content_element);
                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                });
            },
        },

    }
</script>
