<template>
  <div class="form-group">
    <label :for="id" class="form-label">
      {{ label }}
      <span v-if="required" class="form-label-required">*</span>
    </label>
    <div class="form-dropdown">
      <Select 
        :id="id" 
        v-model="internalValue"
        :options="options" 
        :class="{ 'error': error }"
        :disabled="disabled" 
        :placeholder="placeholder" 
        :optionLabel="optionLabel" 
        :optionValue="optionValue"
        :checkmark="true"
        class="w-full"
      />
    </div>
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Select from 'primevue/select'

interface Props {
  id: string
  label: string
  modelValue: any
  options: any[]
  placeholder?: string
  required?: boolean
  disabled?: boolean
  error?: string
  optionLabel?: string
  optionValue?: string
}

interface Emits {
  (e: 'update:modelValue', value: any): void
}

const props = withDefaults(defineProps<Props>(), {
  required: false,
  disabled: false,
  optionLabel: 'label',
  optionValue: 'value'
})

const emit = defineEmits<Emits>()

const internalValue = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})
</script>
