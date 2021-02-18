<template>
  
    <div class="text-red-800 text-lg flex items-center"
         :class="transparent ? '' : 'px-1'"
        >
        <div class="cursor-pointer hover:text-red-500 hover:bg-white px-2 hover:text-red-600 hover:shadow-inner" 
             :class="transparent ? '' : 'bg-gray-100'"
            @click.stop.prevent="$emit('remove')"
        >
            <i class="fas" :class="icon ? icon : 'fa-times'"></i>
        </div>
    </div>

</template>

<script>

    export default {

        props: ['icon', 'transparent'],

    }

</script>
