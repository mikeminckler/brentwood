<template>
  
    <div class="text-red-800 text-lg flex items-center px-1">
        <div class="cursor-pointer hover:text-red-500 hover:bg-white px-2 rounded bg-gray-100 hover:text-red-600 hover:shadow-inner" @click.stop.prevent="$emit('remove')"><i class="fas fa-times"></i></div>
    </div>

</template>
