<template>

    <div class="relative">

        <div class="relative z-2">
            <div class="" v-html="content.code"></div>
        </div>

        <div class="flex relative">
            <div class="flex-2 relative z-2">
                <div class="relative pb-25p my-4">
                    <textarea class="px-4 py-2 font-mono bg-gray-700 text-white absolute w-full h-full" v-model="code"></textarea>
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
                }, 2000),

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
