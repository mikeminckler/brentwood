<template>
    <div class=""></div>
</template>

<script>
    export default {

        data() {
            return {
                setScrollPosition: _.debounce( function() {
                    this.$store.dispatch('setScrollPosition', window.scrollY);
                }, 50),
            }
        },

        computed: {
            scrollPosition() {
                return this.$store.state.scrollPosition;
            }
        },

        mounted() {
            const setScroll = () =>  {
                this.setScrollPosition();
            };
            //window.addEventListener('scroll', setScroll);

            this.$once('hook:destroyed', () => {
                //window.removeEventListener('scroll', setScroll);
            });
        },

    }
</script>
