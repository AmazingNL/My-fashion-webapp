<script setup>
import { flashState, removeFlash } from '../stores/flashStore'
</script>

<template>
  <div class="flash-stack" aria-live="polite" aria-atomic="true">
    <button
      v-for="message in flashState.messages"
      :key="message.id"
      type="button"
      class="flash-message"
      :class="`flash-${message.type}`"
      @click="removeFlash(message.id)"
    >
      <span>{{ message.text }}</span>
    </button>
  </div>
</template>