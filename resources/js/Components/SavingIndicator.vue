<template>

    <div class="py-2 relative flex justify-center text-green-500 border" 
        :class="saving ? 'bg-gray-200 border-gray-200' : 'bg-green-100 border-green-300'" 
        style="transition: background-color var(--transition-time); min-width: 175px;"
    >

        <transition name="saving">
            <div class="flex" v-if="!saving" key="saved">
                <div class=""><i class="fas fa-check"></i></div>
                <div class="ml-2">All Changes Saved</div>
            </div>

            <div class="flex" v-if="saving" key="saving">
                <div class="spin"><i class="fas fa-sync-alt"></i></div>
                <div class="ml-2">Saving</div>
            </div>
        </transition>

    </div>

</template>

<script>
    export default {

        data() {
            return {
                saving: false,
            }
        },

        computed: {
            isSaving() {
                return this.$store.state.saving.length ? true : false;
            }
        },

        watch: {
            isSaving() {
                if (this.isSaving) {
                    this.saving = true;
                } else {
                    this.setSaving();
                }
            }
        },

        methods: {
            setSaving: _.debounce( function() {
                this.saving = this.isSaving;
            }, 500),
        },

    }
</script>

<style>

@keyframes saving {
    0% {
        opacity: 0;
        transform: translateX(-100%);
    }
    100%   {
        opacity: 1;
        transform: translateX(0%);
    }
}

.saving-enter-active {
    animation: saving var(--transition-time) ease-out;
}

.saving-leave-active {
    position: absolute;
    animation: saving var(--transition-time) reverse;
}


</style>
