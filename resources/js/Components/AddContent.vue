<template>

    <div class="">

        <transition-group name="new-content">
            <form-content-element 
                v-for="(contentElement, index) in sortedContentElments"
                :key="contentElement.type + '-' + contentElement.id"
                :value="contentElement"
                @input="updateContentElement($event, contentElement)"
            >
            </form-content-element>
        </transition-group>

        <div class="flex">
            <div class="button mx-2" @click="addTextBlock">
                <div class="pr-2"><i class="fas fa-plus"></i></div>
                <div>Text</div>
            </div>
        </div>

    </div>

</template>

<script>

    import ContentElement from '@/Forms/ContentElement.vue';

    export default {

        components: {
            'form-content-element': ContentElement,
        },

        computed: {
            contentElements() {
                return this.$store.state.page.content_elements;
            },
            sortedContentElments() {
                return this.$lodash.orderBy(this.contentElements, ['sort_order', 'id'], ['desc', 'asc']);
            },
        },

        methods: {

            updateContentElement: function(data, contentElement) {

                let index = this.$lodash.findIndex( this.content_elements, c => {
                    return c.id === contentElement.id;
                });

                this.contentElements.splice(index, 1);

                this.contentElements.push(data);

            },

            addTextBlock: function() {

                let contentElement = {
                    id: '0.' + this.$store.state.page.content_elements.length,
                    type: 'text-block',
                    page_id: this.$store.state.page.id,
                    sort_order: this.$store.state.page.content_elements.length + 1,
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

<style>

@keyframes new-content {
    0% {
        opacity: 0;
        max-height: 0px;
    }
    100% {
        opacity: 1;
        max-height: 250px;
    }
}

.new-content-enter-active {
    animation: new-content var(--transition-time) ease-out;
}

.new-content-leave-active {
    animation: new-content var(--transition-time) reverse;
}

</style>
