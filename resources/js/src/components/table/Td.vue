<template>
  <td
    :class="[
      'whitespace-nowrap px-4 py-1 align-middle text-[12px]',
      $attrs.class
    ]"
  >
    <slot></slot>
  </td>
</template>