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
        :options="filteredOptions" 
        :class="{ 'error': error }"
        :disabled="disabled" 
        :placeholder="placeholder" 
        :optionLabel="optionLabel" 
        :optionValue="optionValue"
        :filter="true"
        :filterPlaceholder="filterPlaceholder"
        :showClear="true"
        :checkmark="true"
        style="width: 100%; height: 2.5rem;"
        @update:modelValue="handleChange"
        @filter="onFilter"
      />
    </div>
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
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
  filterPlaceholder?: string
}

interface Emits {
  (e: 'update:modelValue', value: any): void
}

const props = withDefaults(defineProps<Props>(), {
  required: false,
  disabled: false,
  optionLabel: 'label',
  optionValue: 'value',
  filterPlaceholder: 'Buscar...'
})

const emit = defineEmits<Emits>()

const searchTerm = ref('')

// Ordenar opções alfabeticamente
const sortedOptions = computed(() => {
  return [...props.options].sort((a, b) => {
    const labelA = a[props.optionLabel] || ''
    const labelB = b[props.optionLabel] || ''
    return labelA.localeCompare(labelB, 'pt-BR', { sensitivity: 'base' })
  })
})

// Filtrar opções baseado no termo de busca
const filteredOptions = computed(() => {
  if (!searchTerm.value) {
    return sortedOptions.value
  }
  
  const term = searchTerm.value.toLowerCase()
  return sortedOptions.value.filter(option => {
    const label = (option[props.optionLabel] || '').toLowerCase()
    return label.includes(term)
  })
})

const handleChange = (value: any) => {
  emit('update:modelValue', value)
}

const onFilter = (event: any) => {
  searchTerm.value = event.value || ''
}

// Limpar busca quando o valor muda
watch(() => props.modelValue, () => {
  searchTerm.value = ''
})
</script>

<style scoped>
.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.form-label-required {
  color: #ef4444;
  margin-left: 0.25rem;
}

.form-dropdown {
  position: relative;
}

.form-error {
  color: #ef4444;
  font-size: 0.75rem;
  margin-top: 0.25rem;
}

:deep(.p-dropdown) {
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

:deep(.p-dropdown:not(.p-disabled):hover) {
  border-color: #9ca3af;
}

:deep(.p-dropdown:not(.p-disabled).p-focus) {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

:deep(.p-dropdown.error) {
  border-color: #ef4444;
}

:deep(.p-dropdown.error:not(.p-disabled).p-focus) {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

:deep(.p-dropdown-panel) {
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

:deep(.p-dropdown-filter) {
  padding: 0.75rem;
  border-bottom: 1px solid #e5e7eb;
}

:deep(.p-dropdown-filter .p-inputtext) {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.25rem;
  font-size: 0.875rem;
}

:deep(.p-dropdown-filter .p-inputtext:focus) {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

:deep(.p-dropdown-items) {
  max-height: 200px;
  overflow-y: auto;
}

:deep(.p-dropdown-item) {
  padding: 0.75rem;
  font-size: 0.875rem;
  color: #374151;
  transition: background-color 0.15s ease-in-out;
}

:deep(.p-dropdown-item:hover) {
  background-color: #f3f4f6;
}

:deep(.p-dropdown-item.p-highlight) {
  background-color: #dbeafe;
  color: #1e40af;
}

:deep(.p-dropdown-item.p-highlight:hover) {
  background-color: #bfdbfe;
}
</style>
