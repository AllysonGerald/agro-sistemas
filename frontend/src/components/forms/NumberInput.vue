<template>
  <div class="form-group">
    <label :for="id" class="form-label">
      {{ label }}
      <span v-if="required" class="form-label-required">*</span>
    </label>
    <div class="form-input-number">
      <input 
        :id="id" 
        :value="modelValue" 
        type="text"
        class="form-input"
        :class="inputClass"
        :placeholder="placeholder"
        :disabled="disabled"
        style="height: 2.5rem;"
        @input="handleInput"
      />
      <div class="input-arrows">
        <div class="input-arrow" @click="increment">▲</div>
        <div class="input-arrow" @click="decrement">▼</div>
      </div>
    </div>
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  id: string
  label: string
  modelValue: string | number
  placeholder?: string
  required?: boolean
  disabled?: boolean
  error?: string
  size?: 'normal' | 'small'
  step?: number
}

interface Emits {
  (e: 'update:modelValue', value: string): void
}

const props = withDefaults(defineProps<Props>(), {
  required: false,
  disabled: false,
  size: 'normal',
  step: 1
})

const emit = defineEmits<Emits>()

const inputClass = computed(() => {
  const classes = []
  if (props.error) classes.push('error')
  if (props.modelValue && !props.error) classes.push('success')
  if (props.size === 'small') classes.push('area-input')
  return classes
})

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  emit('update:modelValue', target.value)
}

const increment = () => {
  if (props.disabled) return
  const currentValue = parseFloat(props.modelValue.toString()) || 0
  const newValue = currentValue + props.step
  emit('update:modelValue', newValue.toString())
}

const decrement = () => {
  if (props.disabled) return
  const currentValue = parseFloat(props.modelValue.toString()) || 0
  const newValue = Math.max(0, currentValue - props.step)
  emit('update:modelValue', newValue.toString())
}
</script>
