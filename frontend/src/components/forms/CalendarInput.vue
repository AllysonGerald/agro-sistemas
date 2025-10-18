<template>
  <div class="form-group">
    <label :for="id" class="form-label">
      {{ label }}
      <span v-if="required" class="form-label-required">*</span>
    </label>
    <div class="form-dropdown">
      <DatePicker 
        :id="id" 
        :modelValue="modelValue" 
        :class="{ 'error': error }"
        :disabled="disabled" 
        :placeholder="placeholder" 
        :dateFormat="dateFormat"
        style="width: 100%; height: 2.5rem;"
        @update:modelValue="handleChange"
      />
    </div>
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script setup lang="ts">
import DatePicker from 'primevue/datepicker'

interface Props {
  id: string
  label: string
  modelValue: any
  placeholder?: string
  required?: boolean
  disabled?: boolean
  error?: string
  dateFormat?: string
}

interface Emits {
  (e: 'update:modelValue', value: any): void
}

withDefaults(defineProps<Props>(), {
  required: false,
  disabled: false,
  dateFormat: 'dd/mm/yy'
})

const emit = defineEmits<Emits>()

const handleChange = (event: any) => {
  emit('update:modelValue', event)
}
</script>
