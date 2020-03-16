<template>

    <div class="">


        <div class="" v-for="contentElement in newContentElements">
            <component :is="contentElement.type" :content="contentElement.content"></component>
        </div>

        <div class="flex">
            <div class="button mx-2" @click="addTextBlock">
                <div class="pr-2"><i class="fas fa-plus"></i></div>
                <div>Text</div>
            </div>
        </div>

    </div>

</template>

<script>

    import TextBlock from '@/Forms/TextBlock.vue';

    export default {

        components: {
            'text-block': TextBlock,
        },

        computed: {
            newContentElements() {
                return this.$lodash.filter(this.$store.state.page.content_elements, contentElement => {
                    return contentElement.id < 1;
                });
            },
        },

        methods: {

            addTextBlock: function() {

                let contentElement = {
                    id: '0.' + this.$store.state.page.content_elements.length,
                    type: 'text-block',
                    content: {
                        header: '',
                        body: '',
                    }
                };

                this.$store.dispatch('addContentElement', contentElement);

            }
        },

    }
</script>
