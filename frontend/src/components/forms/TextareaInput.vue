<template>
  <div class="form-group">
    <label :for="id" class="form-label">
      {{ label }}
      <span v-if="required" class="form-label-required">*</span>
    </label>
    <textarea 
      :id="id" 
      :value="modelValue" 
      class="form-input form-textarea"
      :class="{ 
        'error': error, 
        'success': modelValue && !error 
      }"
      :placeholder="placeholder"
      :rows="rows"
      :disabled="disabled"
      style="min-height: 2.5rem; height: auto;"
      @input="handleInput"
    ></textarea>
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  id: string
  label: string
  modelValue: string
  placeholder?: string
  required?: boolean
  disabled?: boolean
  error?: string
  rows?: number
}

interface Emits {
  (e: 'update:modelValue', value: string): void
}

withDefaults(defineProps<Props>(), {
  required: false,
  disabled: false,
  rows: 3
})

const emit = defineEmits<Emits>()

const handleInput = (event: Event) => {
  const target = event.target as HTMLTextAreaElement
  emit('update:modelValue', target.value)
}
</script>
