<script setup>
import FlashMessages from './components/FlashMessages.vue'
import './stores/themeStore'
</script>

<template>
  <div class="app-shell">
    <FlashMessages />
    <RouterView />
  </div>
</template>
