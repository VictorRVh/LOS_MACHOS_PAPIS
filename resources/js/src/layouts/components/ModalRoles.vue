<template>
  <div class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center" @click.self="$emit('close')">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-[90%] max-w-md p-6">
      <!-- Título -->
      <header class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
        <slot name="title" />
      </header>

      <!-- Contenido principal -->
      <section class="mb-4 text-gray-700 dark:text-gray-200">
        <slot name="body" />
      </section>

      <!-- Footer -->
      <footer class="text-right">
        <slot name="footer" />
      </footer>
    </div>
  </div>
</template>

