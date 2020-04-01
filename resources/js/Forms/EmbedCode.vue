<template>

    <div class="relative">

        <div class="relative">
            <div class="" v-html="content.code"></div>
        </div>

        <div class="flex relative">

            <div class="flex-1 relative flex flex-col"></div>

            <div class="flex-2 relative">
                <div class="relative pb-33p">
                    <textarea class="p-2 absolute w-full h-full" v-model="code"></textarea>
                </div>
            </div>

        </div>
    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';

    export default {

        props: [ 'content', 'uuid', 'first'],
        mixins: [ Feedback ],

        components: {
        },

        data() {
            return {
                code: '',
            }
        },

        computed: {
        },

        watch: {
            'code': _.debounce( function() {
                    if (this.content.code != this.code) {
                        this.content.code = this.code;
                    }
                }, 1000),

            'content.code': function() {
                if (this.code != this.content.code) {
                    this.code = this.content.code;
                }
            },
        },

        mounted() {
            if (this.content.code) {
                this.code = this.content.code;
            }
        },

        methods: {
        },

    }
</script>
