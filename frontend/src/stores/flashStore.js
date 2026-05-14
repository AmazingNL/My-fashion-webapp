import { reactive } from 'vue'

let nextId = 1

export const flashState = reactive({
  messages: [],
})

export function addFlash(message, type = 'success') {
  const text = String(message || '').trim()
  if (!text) return

  const id = nextId++
  flashState.messages.push({ id, text, type })

  window.setTimeout(() => removeFlash(id), 4200)
}

export function removeFlash(id) {
  const index = flashState.messages.findIndex(message => message.id === id)
  if (index >= 0) {
    flashState.messages.splice(index, 1)
  }
}