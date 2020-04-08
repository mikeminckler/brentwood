<template>

    <div class="flex flex-col items-center justify-center" :class="photo ? 'h-full text-white text-shadow' : 'text-gray-500'">
        <div class="">
            <input class="text-center stat-number" type="text" v-model="model.stat_number" placeholder="00" />
        </div>
        <div class="">
            <input class="text-center stat-name" type="text" v-model="model.stat_name" placeholder="Statistic Name" />
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
        },

        watch: {
        },

        mounted() {
        },

        methods: {
        },

    }
</script>
