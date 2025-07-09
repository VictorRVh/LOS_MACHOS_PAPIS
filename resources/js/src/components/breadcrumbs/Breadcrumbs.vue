<template>
  <nav class="flex gap-2 items-center text-sm">
    <template v-for="(breadcrumb, index) in breadcrumbs" :key="breadcrumb.name">
      <!-- Enlace si tiene 'to' y no es el último -->
      <RouterLink
        v-if="breadcrumb.to && index !== breadcrumbs.length - 1"
        :to="breadcrumb.to"
        class="text-blue-600 hover:underline uppercase"
      >
        {{ breadcrumb.name }}
      </RouterLink>

      <!-- Texto plano para el último o sin 'to' -->
      <span v-else class="uppercase text-gray-500">
        {{ breadcrumb.name }}
      </span>

      <!-- Separador -->
      <span v-if="index < breadcrumbs.length - 1">/</span>
    </template>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

const breadcrumbs = computed(() => {
  return route.matched
    .flatMap(r => {
      const metaBreadcrumb = r.meta.breadcrumb
      // Si el breadcrumb es función, ejecútala con la route
      if (typeof metaBreadcrumb === 'function') {
        return metaBreadcrumb(route)
      }
      return metaBreadcrumb || []
    })
    .filter((item, index, arr) =>
      // Evitar duplicados (por nombre o ruta)
      index === arr.findIndex(i => i.name === item.name)
    )
})
</script>
