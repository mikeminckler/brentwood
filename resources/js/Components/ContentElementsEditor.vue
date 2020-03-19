<template>

    <div class="relative">

        <transition-group name="new-content">
            <form-content-element 
                v-for="(contentElement, index) in sortedContentElments"
                :key="contentElement.type + '-' + contentElement.id"
                :value="contentElement"
                @input="updateContentElement($event, contentElement)"
            >
            </form-content-element>
        </transition-group>

        <div class="flex w-full bg-gray-200 p-2 relative z-2 shadow mt-4 items-center">
            <div class="font-semibold">Create New</div>
            <div class="button mx-2" @click="addTextBlock">
                <div class="pr-2"><i class="fas fa-align-justify"></i></div>
                <div>Text</div>
            </div>

            <div class="button mx-2" @click="addPhotoBlock">
                <div class="pr-2"><i class="fas fa-file-image"></i></div>
                <div>Photos</div>
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
                return this.$lodash.orderBy(this.contentElements, ['sort_order', 'id'], ['asc', 'asc']);
            },
        },

        methods: {

            updateContentElement: function(data, contentElement) {

                console.log('UPDATE CE: ' + data.id);

                let ce = this.$lodash.find( this.content_elements, c => {
                    return c.id === contentElement.id;
                });

                this.$lodash.merge(ce, data);

                //this.contentElements.splice(index, 1);
                //this.contentElements.push(data);

            },

            newContentElement: function() {
                return {
                    id: '0.' + this.$store.state.page.content_elements.length,
                    page_id: this.$store.state.page.id,
                    sort_order: this.$store.state.page.content_elements.length + 1,
                };
            },

            addTextBlock: function() {

                let contentElement = this.newContentElement();

                contentElement.type = 'text-block';
                contentElement.content = {
                    id: 0,
                    header: '',
                    body: '',
                };

                this.$store.dispatch('addContentElement', contentElement);

            },

            addPhotoBlock: function() {
                
                let contentElement = this.newContentElement();

                contentElement.type = 'photo-block';
                contentElement.content = {
                    id: 0,
                    photos: [],
                    columns: 1,
                    height: 33,
                    padding: false,
                    show_text: false,
                    header: '',
                    body: '',
                    text_order: 1,
                    text_span: 1,
                };

                this.$store.dispatch('addContentElement', contentElement);
            },
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
