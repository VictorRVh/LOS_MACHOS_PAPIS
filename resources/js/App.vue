<script setup>
import LayoutRenderer from './src/layouts/LayoutRenderer.vue';
import ConfirmModal from './src/components/page/ConfirmModal.vue';

import useTheme from './src/composables/useTheme';
useTheme();
</script>

<template>
    <LayoutRenderer class="overflow-x-hidden" />
    <!-- confirm modal -->
    <ConfirmModal />
</template>
