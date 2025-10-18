<template>
  <div class="form-group">
    <label :for="id" class="form-label">
      {{ label }}
      <span v-if="required" class="form-label-required">*</span>
    </label>
    <input 
      :id="id" 
      :value="modelValue" 
      :type="type"
      class="form-input"
      :class="{ 
        'error': error, 
        'success': modelValue && !error 
      }"
      :placeholder="placeholder"
      :disabled="disabled"
      style="height: 2.5rem;"
      @input="handleInput"
    />
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  id: string
  label: string
  modelValue: string
  type?: 'text' | 'email' | 'tel' | 'password'
  placeholder?: string
  required?: boolean
  disabled?: boolean
  error?: string
}

interface Emits {
  (e: 'update:modelValue', value: string): void
  (e: 'input', value: string): void
}

withDefaults(defineProps<Props>(), {
  type: 'text',
  required: false,
  disabled: false
})

const emit = defineEmits<Emits>()

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  emit('update:modelValue', target.value)
  emit('input', target.value)
}
</script>
