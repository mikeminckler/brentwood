<template>

    <div class="relative bottom-0 flex flex-col w-full z-3 p-8 items-center" :class="textClasses">
        <div class="">
            <input class="font-oswald font-normal whitespace-nowrap h-24 -my-4" :class="[photo ? (photo.span > 1 ? 'text-right' : 'text-center' ) : 'text-center', model.stat_number ? ( model.stat_number.length > 6 ? 'text-6xl' : 'text-8xl' ) : 'text-8xl' ]" type="text" v-model="model.stat_number" placeholder="00" />
        </div>
        <div class="w-full mt-0">
            <input class="font-oswald text-xl md:text-3xl text-center" :class="photo ? (photo.span > 1 ? 'text-right' : 'text-center' ) : 'text-center'" type="text" v-model="model.stat_name" placeholder="Statistic Name" />
        </div>
        <div v-if="model.stat_number && model.stat_name" class="h-1 w-16 my-2" :class="photo ? 'bg-white' : 'bg-gray-400'"></div>
    </div>

</template>

<script>
    export default {

        props: ['model', 'photo'],
        data() {
            return {
            
            }
        },

        computed: {
            textClasses() {
                if (this.photo) {

                    let classes = 'text-white text-shadow';

                    if (this.photo.span > 1) {
                        classes += ' items-end h-full';
                    } else {
                        classes += ' items-center';
                    }
                    return classes;
                } else {
                    return 'text-gray-500';
                }
            }
        },

        watch: {
        },

        mounted() {
        },

        methods: {
        },

    }
</script>
