<!-- <script setup>
defineProps({
    show: {
        type: Boolean,
        default: () => false,
    },
    title: {
        type: String,
    },
    sliderClass: {
        type: String,
        default: () => 'w-[520px]',
    },
    transitionClass: {
        type: String,
        default: () => 'right-[-520px]',
    },
    transitionActiveClass: {
        type: String,
        default: () => 'transition-all duration-300',
    },
});
const emit = defineEmits(['hide']);
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="w-screen h-screen fixed bg-gray-200 z-[308] top-0 left-0 opacity-20"
            @click="emit('hide', false)"
        ></div>
    </Teleport>

    <Teleport to="body">
        <Transition
            :enter-from-class="transitionClass"
            :leave-to-class="transitionClass"
            :enter-active-class="transitionActiveClass"
            :leave-active-class="transitionActiveClass"
        >
            <div
                v-if="show"
                class="h-[calc(100vh-80px)] fixed max-w-full top-20 z-[309] bg-white dark:bg-gray-800 border-l border-gray-400 dark:border-gray-700 shadow-google right-0 slider-container"
                :class="[sliderClass]"
            >
                <div
                    class="w-full h-full p-4 overflow-y-auto overflow-x-hidden"
                >
                    <div class="flex-between w-full">
                        <slot name="title">
                            <div class="font-bold text-lg">
                                {{ title ? title : 'Create or update data' }}
                            </div>
                        </slot>

                        <div class="flex-start">
                            <div class="mr-4">
                                <slot name="before_close"> </slot>
                            </div>

                            <slot name="close">
                                <span
                                    class="text-xl cursor-pointer"
                                    @click="emit('hide', false)"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        width="24"
                                        height="24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        fill="none"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="css-i6dzq1"
                                    >
                                        <line
                                            x1="18"
                                            y1="6"
                                            x2="6"
                                            y2="18"
                                        ></line>
                                        <line
                                            x1="6"
                                            y1="6"
                                            x2="18"
                                            y2="18"
                                        ></line>
                                    </svg>
                                </span>
                            </slot>
                        </div>
                    </div>
                    <slot></slot>
                </div>
            </div>
        </Transition>
    </Teleport>
</template> -->


<script setup>
defineProps({
    show: {
        type: Boolean,
        default: () => false,
    },
    title: {
        type: String,
    },
});
const emit = defineEmits(['hide']);
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
                @click.self="emit('hide', false)"
            >
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 relative"
                >
                    <div class="flex justify-between items-start mb-4">
                        <slot name="title">
                            <h2 class="font-bold text-lg text-cetpro dark:text-cetpro-light">
                                {{ title ? title : 'Crear o Actualizar Datos' }}
                            </h2>
                        </slot>

                        <div class="flex items-center gap-3">
                            <slot name="before_close" />
                            <slot name="close">
                                <button
                                    @click="emit('hide', false)"
                                    class="text-xl text-gray-500 hover:text-red-600"
                                >
                                    ✕
                                </button>
                            </slot>
                        </div>
                    </div>

                    <slot />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
