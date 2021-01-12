export default {

    methods: {
        /*
        saveContent: _.debounce( function() {
           this.$eventer.$emit('save-content', this.uuid);
        }, 1000),
        */

        saveContent: function() {
           this.$eventer.$emit('save-content', this.uuid);
        },
    },

}
