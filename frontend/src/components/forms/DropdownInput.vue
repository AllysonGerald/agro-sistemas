<template>
  <div class="form-group">
    <label :for="id" class="form-label">
      {{ label }}
      <span v-if="required" class="form-label-required">*</span>
    </label>
    <div class="form-dropdown">
      <Select 
        :id="id" 
        :modelValue="modelValue" 
        :options="options" 
        :class="{ 'error': error }"
        :disabled="disabled" 
        :placeholder="placeholder" 
        :optionLabel="optionLabel" 
        :optionValue="optionValue"
        :checkmark="true"
        style="width: 100%; height: 2.5rem;"
        @update:modelValue="handleChange"
      />
    </div>
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script setup lang="ts">
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

withDefaults(defineProps<Props>(), {
  required: false,
  disabled: false,
  optionLabel: 'label',
  optionValue: 'value'
})

const emit = defineEmits<Emits>()

const handleChange = (value: any) => {
  emit('update:modelValue', value)
}
</script>
