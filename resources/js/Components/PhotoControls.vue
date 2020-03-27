<template>

    <div class="photo-icons text-white" v-if="photo">

        <div class="relative">
            <div class="absolute right-0 bottom-0 transform rotate-90 origin-top-right w-32">
                <div class="flex items-center px-2 py-1">
                    <input type="range" v-model="photo.offsetY" min="0" max="100" />
                </div>
            </div>

            <div class="absolute right-0 bottom-0 w-32">
                <div class="flex items-center px-2 py-1">
                    <input type="range" v-model="photo.offsetX" min="0" max="100" />
                </div>
            </div>
        </div>

        <div class="flex">
            <div class="flex flex-1 px-2">
                <div class="cursor-pointer" @click="photo.fill = !photo.fill" v-if="fill">
                    <div v-if="photo.fill"><i class="fas fa-expand"></i></div>
                    <div v-if="!photo.fill"><i class="fas fa-compress"></i></div>
                </div>

                <div class="cursor-pointer mx-1" v-if="photo.sort_order > 1 && photos.length > 1 && sort" @click="$emit('sortUp')"><i class="fas fa-arrow-alt-circle-left"></i></div>
                <div class="cursor-pointer mx-1" v-if="photo.sort_order < photos.length && photos.length > 1 && sort" @click="$emit('sortDown')"><i class="fas fa-arrow-alt-circle-right"></i></div>
                <div class="cursor-pointer mx-1" v-if="photo.span < content.columns && content.columns > 1 && span" @click="photo.span++"><i class="fas fa-plus-circle"></i></div>
                <div class="cursor-pointer mx-1" v-if="photo.span > 1 && content.columns > 1 && span" @click="photo.span--"><i class="fas fa-minus-circle"></i></div>
            </div>
            <div class="mx-1 remove-icon" @click="$emit('remove')"><i class="fas fa-times"></i></div>
        </div>

        <div class="px-2 mb-1">
            <input type="text" placeholder="caption" v-model="photo.description" />
        </div>

    </div>

</template>

<script>
    export default {
        props: ['photo', 'fill', 'sort', 'span', 'content', 'photos'],
    }
</script>
